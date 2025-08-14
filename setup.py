#!/usr/bin/env python3
"""
Automates initial setup for a PHP/Apache app:
- Ensures Apache modules: headers, rewrite
- Runs `composer install`
- Copies config/config.dist.php -> config/config.php (if missing)
- Sets 'install.password' in config.php
- Warns about missing PHP extensions
- Fixes permissions on common writable dirs
- Optionally reloads Apache

Usage:
  python3 run python install.py --install-password 'SECRET' --web-user www-data --restart-apache
"""

import argparse
import os
import re
import shutil
import subprocess
import sys
from pathlib import Path

APP_ROOT = Path(__file__).resolve().parent

WRITABLE_DIRS = [
    APP_ROOT / "uploads",
    APP_ROOT / "uploads" / "images",
    APP_ROOT / "uploads" / "attachments",
    APP_ROOT / "cache",
    APP_ROOT / "tpl_c",
    APP_ROOT / "templates_c",
    APP_ROOT / "storage",
    APP_ROOT / "logs",
]

REQUIRED_PHP_EXTS = [
    "pdo", "pdo_mysql", "mbstring", "intl", "curl", "gd", "xml", "json"
]

def run(cmd, check=True, capture=False, env=None):
    """Run a shell command with nice errors."""
    try:
        if capture:
            return subprocess.run(cmd, check=check, text=True, capture_output=True, env=env)
        else:
            return subprocess.run(cmd, check=check, text=True, env=env)
    except FileNotFoundError:
        print(f"ERROR: command not found: {cmd[0]}", file=sys.stderr)
        sys.exit(3)
    except subprocess.CalledProcessError as e:
        if capture:
            print(e.stdout, end="")
            print(e.stderr, file=sys.stderr, end="")
        raise

def which(name: str) -> str | None:
    return shutil.which(name)

def need(cmd_name: str):
    if which(cmd_name) is None:
        print(f"ERROR: Missing dependency: {cmd_name}", file=sys.stderr)
        sys.exit(3)

def apache_has_module(mod: str) -> bool:
    """Check via `apachectl -M` if <mod>_module is loaded."""
    need("apachectl")
    out = run(["apachectl", "-M"], capture=True).stdout.splitlines()
    needle = f" {mod}_module"
    return any(line.strip().startswith(needle) for line in out)

def enable_apache_module(mod: str):
    """Enable module via a2enmod when available."""
    if apache_has_module(mod):
        print(f"Apache module '{mod}' already enabled.")
        return
    a2enmod = which("a2enmod")
    if not a2enmod:
        print(f"WARN: a2enmod not found; enable '{mod}' manually.", file=sys.stderr)
        return
    print(f"Enabling Apache module '{mod}'...")
    run(["sudo", a2enmod, mod], check=True)

def composer_install():
    need("composer")
    print("Running composer install...")
    run(["composer", "install", "--no-interaction", "--prefer-dist", "--no-progress"])

def provision_config(install_password: str):
    cfg_dist = APP_ROOT / "config" / "config.dist.php"
    cfg = APP_ROOT / "config" / "config.php"

    if not cfg.exists():
        if not cfg_dist.exists():
            print("ERROR: config/config.dist.php not found.", file=sys.stderr)
            sys.exit(5)
        print("Creating config/config.php from config/config.dist.php...")
        cfg.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(cfg_dist, cfg)
    else:
        print("config/config.php already exists; not overwriting.")

    # Replace 'install.password' => '...'
    txt = cfg.read_text(encoding="utf-8", errors="replace")
    if "install.password" in txt:
        print("Setting install.password in config/config.php...")
        # Regex: capture the key and replace only the quoted value
        pattern = r"('install\.password'\s*=>\s*)'[^']*'"
        repl = r"\1'{}'".format(install_password.replace("\\", "\\\\").replace("'", "\\'"))
        new_txt, n = re.subn(pattern, repl, txt)
        if n == 0:
            print("WARN: 'install.password' key found but not replaced; check file format.", file=sys.stderr)
        if new_txt != txt:
            cfg.write_text(new_txt, encoding="utf-8")
    else:
        print("WARN: 'install.password' key not found in config/config.php; set it manually.", file=sys.stderr)

def php_missing_extensions() -> list[str]:
    need("php")
    out = run(["php", "-m"], capture=True).stdout
    mods = {line.strip().lower() for line in out.splitlines() if line.strip()}
    missing = [ext for ext in REQUIRED_PHP_EXTS if ext.lower() not in mods]
    return missing

def fix_permissions(web_user: str):
    for d in WRITABLE_DIRS:
        if d.is_dir():
            print(f"Ensuring write permission for {web_user} on {d}")
            run(["sudo", "chown", "-R", f"{web_user}:{web_user}", str(d)], check=False)
            # 775 for dirs, 664 for files
            run(["sudo", "find", str(d), "-type", "d", "-exec", "chmod", "775", "{}", "+"], check=False)
            run(["sudo", "find", str(d), "-type", "f", "-exec", "chmod", "664", "{}", "+"], check=False)

def reload_apache():
    systemctl = which("systemctl")
    if systemctl:
        print("Reloading Apache...")
        # Try reload, fallback to restart
        res = run(["sudo", systemctl, "reload", "apache2"], check=False)
        if res.returncode != 0:
            run(["sudo", systemctl, "restart", "apache2"], check=True)
    else:
        print("WARN: systemctl not found; reload Apache manually.", file=sys.stderr)

def main():
    parser = argparse.ArgumentParser(description="Automate PHP/Apache app install.")
    parser.add_argument("--install-password", required=True, help="Value for 'install.password' in config.php")
    parser.add_argument("--web-user", default="www-data", help="Web server user (default: www-data)")
    parser.add_argument("--restart-apache", action="store_true", help="Reload/restart Apache at the end")
    args = parser.parse_args()

    # Basic sanity
    need("php")
    need("composer")
    need("apachectl")

    # Validate web user exists
    try:
        import pwd  # POSIX only
        pwd.getpwnam(args.web_user)
    except Exception:
        print(f"ERROR: web user '{args.web_user}' does not exist", file=sys.stderr)
        sys.exit(4)

    # Apache modules
    enable_apache_module("headers")
    enable_apache_module("rewrite")

    # Composer
    composer_install()

    # Config
    provision_config(args.install_password)

    # PHP exts (non-fatal)
    missing = php_missing_extensions()
    if missing:
        print("WARN: missing PHP extensions:", " ".join(missing))
        print("      e.g., sudo apt-get install php-mysql php-mbstring php-intl php-curl php-gd php-xml")

    # Permissions
    fix_permissions(args.web_user)

    # Apache reload (optional)
    if args.restart_apache:
        reload_apache()
    else:
        print("Skipping Apache reload (pass --restart-apache to enable).")

    print("✅ Install completed.")

if __name__ == "__main__":
    main()
