<?php

require_once(ROOT_DIR . 'Controls/Dashboard/DashboardItem.php');
require_once(ROOT_DIR . 'Presenters/Dashboard/AnnouncementPresenter.php');

class AnnouncementsControl extends DashboardItem implements IAnnouncementsControl
{
    private $presenter;

    public function __construct(SmartyPage $smarty)
    {
        parent::__construct($smarty);
        $this->presenter = new AnnouncementPresenter($this, new AnnouncementRepository(), PluginManager::Instance()->LoadPermission());
    }

    public function PageLoad()
    {
        Log::Error('Dashboard widget: Announcements started');
        $t = microtime(true);
        $this->presenter->PageLoad();
        Log::Error('Dashboard widget: Announcements completed in %.2fs', microtime(true) - $t);
        $this->Display('announcements.tpl');
    }

    public function SetAnnouncements($announcements)
    {
        $this->Assign('Announcements', $announcements);
    }
}

interface IAnnouncementsControl
{
    public function SetAnnouncements($announcements);
}
