<?php
error_reporting(E_ALL & ~E_NOTICE);
array (
'settings' =>
array (
'app.title' => 'LibreBooking',
'app.debug' => false,
'admin.email' => 'admin@example.com',
'admin.email.name' => 'LB Administrator',
'company.name' => '',
'company.url' => '',
'default.timezone' => 'Europe/London',
'default.language' => 'en_us',
'script.url' => '',
'install.password' => '',
'cache.templates' => true,
'use.local.js.libs' => false,
'inactivity.timeout' => 30,
'home.url' => '',
'logout.url' => '',
'default.homepage' => 1,
'css.extension.file' => '',
'css.theme' => 'default',
'name.format' => '{first} {last}',
'pages' =>
array (
'configuration.enabled' => true,
),
'database' =>
array (
'type' => 'mysql',
'hostspec' => '127.0.0.1',
'name' => 'librebooking',
'user' => 'lb_user',
'password' => 'password',
),
'phpmailer' =>
array (
'mailer' => 'smtp',
'smtp.host' => '',
'smtp.port' => 25,
'smtp.secure' => '',
'smtp.auth' => true,
'smtp.username' => '',
'smtp.password' => '',
'sendmail.path' => '/usr/sbin/sendmail',
'smtp.debug' => false,
),
'email' =>
array (
'enabled' => true,
'default.from.address' => '',
'default.from.name' => '',
'enforce.custom.template' => false,
),
'logging' =>
array (
'folder' => '/var/log/librebooking/log',
'level' => 'none',
'sql' => false,
),
'uploads' =>
array (
'image.upload.directory' => 'Web/uploads/images',
'image.upload.url' => 'uploads/images',
'reservation.attachments.enabled' => false,
'reservation.attachment.path' => 'uploads/reservation',
'reservation.attachment.extensions' => 'txt,jpg,gif,png,doc,docx,pdf,xls,xlsx,ppt,pptx,csv',
),
'reservation.notify' =>
array (
'application.admin.add' => false,
'application.admin.update' => false,
'application.admin.delete' => false,
'application.admin.approval' => false,
'group.admin.add' => false,
'group.admin.update' => false,
'group.admin.delete' => false,
'group.admin.approval' => false,
'resource.admin.add' => false,
'resource.admin.update' => false,
'resource.admin.delete' => false,
'resource.admin.approval' => false,
),
'schedule' =>
array (
'auto.scroll.today' => true,
'show.week.numbers' => false,
'hide.blocked.periods' => false,
'show.inaccessible.resources' => true,
'reservation.label' => '{name}',
'use.per.user.colors' => false,
'update.highlight.minutes' => 0,
'fast.reservation.load' => false,
'load.mobile.views' => true,
),
'reservation' =>
array (
'prevent.participation' => false,
'prevent.recurrence' => false,
'allow.guest.participation' => false,
'allow.wait.list' => false,
'start.time.constraint' => 'future',
'updates.require.approval' => false,
'title.required' => false,
'description.required' => false,
'checkin.minutes.prior' => 5,
'checkin.admin.only' => false,
'checkout.admin.only' => false,
'reminders.enabled' => false,
'default.start.reminder' => '',
'default.end.reminder' => '',
),
'reservation.labels' =>
array (
'ics.summary' => '{title}',
'ics.my.summary' => '{title}',
'rss.description' => '
Start {startdate}
End {enddate}
Organizer {name}
Description {description}
',
'my.calendar' => '{resourcename} {title}',
'resource.calendar' => '{name}',
'reservation.popup' => '',
),
'reports' =>
array (
'allow.all.users' => false,
),
'registration' =>
array (
'allow.self.registration' => true,
'captcha.enabled' => true,
'require.email.activation' => false,
'auto.subscribe.email' => false,
'notify.admin' => false,
'require.phone' => false,
'require.position' => false,
'require.organization' => false,
'hide.phone' => false,
'hide.position' => false,
'hide.organization' => false,
),
'resource' =>
array (
'contact.is.user' => false,
),
'tablet.view' =>
array (
'allow.guest.reservations' => false,
'auto.suggest.emails' => false,
),
'ics' =>
array (
'subscription.key' => '',
'future.days' => 30,
'past.days' => 0,
),
'cleanup' =>
array (
'years.old.data' => 3,
'delete.old.announcements' => false,
'delete.old.blackouts' => false,
'delete.old.reservations' => false,
),
'password' =>
array (
'disable.reset' => false,
'minimum.letters' => 6,
'minimum.numbers' => 0,
'upper.and.lower' => false,
),
'privacy' =>
array (
'view.schedules' => true,
'view.reservations' => false,
'hide.user.details' => false,
'hide.reservation.details' => false,
'allow.guest.reservations' => false,
'public.future.days' => 30,
),
'recaptcha' =>
array (
'enabled' => false,
'public.key' => '',
'private.key' => '',
'request.method' => 'curl',
),
'security' =>
array (
'headers' => false,
'strict-transport' => 'max-age=31536000',
'x-frame' => 'deny',
'x-xss' => '1, mode=block',
'x-content-type' => 'nosniff',
'content-security-policy' => '',
),
'credits' =>
array (
'enabled' => false,
'allow.purchase' => false,
),
'google.analytics.tracking.id' => '',
'slack.token' => '',
'authentication' =>
array (
'hide.login.prompt' => false,
'captcha.on.login' => false,
'required.email.domains' => '',
'google.login.enabled' => false,
'google.client.id' => '',
'google.client.secret' => '',
'google.redirect.uri' => '/Web/google-auth.php',
'microsoft.login.enabled' => false,
'microsoft.client.id' => '',
'microsoft.tenant.id' => 'common',
'microsoft.client.secret' => '',
'microsoft.redirect.uri' => '/Web/microsoft-auth.php',
'facebook.login.enabled' => false,
'facebook.client.id' => '',
'facebook.client.secret' => '',
'facebook.redirect.uri' => '/Web/facebook-auth.php',
'keycloak.login.enabled' => false,
'keycloak.url' => '',
'keycloak.realm' => '',
'keycloak.client.id' => '',
'keycloak.client.secret' => '',
'keycloak.client.uri' => '/Web/keycloak-auth.php',
'oauth2.login.enabled' => false,
'oauth2.name' => 'OAuth2',
'oauth2.url.authorize' => '',
'oauth2.url.token' => '',
'oauth2.url.userinfo' => '',
'oauth2.client.id' => '',
'oauth2.client.secret' => '',
'oauth2.client.uri' => '/Web/oauth2-auth.php',
),
'plugins' =>
array (
'authentication' => '',
'authorization' => '',
'export' => '',
'permission' => '',
'postregistration' => '',
'prereservation' => '',
'postreservation' => '',
'styling' => '',
),
'api' =>
array (
'enabled' => false,
'registration.allow.self' => false,
'authentication.group' => '',
'accessories.ro.group' => '',
'accounts.ro.group' => '',
'accounts.rw.group' => '',
'attributes.ro.group' => '',
'groups.ro.group' => '',
'reservations.ro.group' => '',
'reservations.rw.group' => '',
'resources.ro.group' => '',
'schedules.ro.group' => '',
'users.ro.group' => '',
),
),
) ?>
