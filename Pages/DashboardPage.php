<?php

require_once(ROOT_DIR . 'Pages/SecurePage.php');
require_once(ROOT_DIR . 'Presenters/DashboardPresenter.php');

class DashboardPage extends SecurePage implements IDashboardPage
{
    private $items = [];

    /**
     * @var DashboardPresenter
     */
    private $_presenter;

    public function __construct()
    {
        parent::__construct('MyDashboard');
        $this->_presenter = new DashboardPresenter($this);
    }

    public function PageLoad()
    {
        $userId = ServiceLocator::GetServer()->GetUserSession()->UserId;
        Log::Error('Dashboard: PageLoad started. userId=%s session_id=%s', $userId, session_id());
        $t0 = microtime(true);

        $this->_presenter->Initialize();
        Log::Error('Dashboard: widgets initialized in %.2fs, widget_count=%d', microtime(true) - $t0, count($this->items));

        $this->Set('items', $this->items);
        $this->Display('dashboard.tpl');

        Log::Error('Dashboard: PageLoad completed in %.2fs', microtime(true) - $t0);
    }

    public function AddItem(DashboardItem $item)
    {
        $this->items[] = $item;
    }
}

interface IDashboardPage
{
    public function AddItem(DashboardItem $item);
}
