<?php


defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */


/**
 * Class DashboardLms
 */
abstract class DashboardBlockLms extends Model
{
    const TYPE_4COL = '4-col';
    const TYPE_3COL = '3-col';
    const TYPE_2COL = '2-col';
    const TYPE_1COL = '1-col';

    const ALLOWED_TYPES = [self::TYPE_4COL, self::TYPE_3COL, self::TYPE_2COL, self::TYPE_1COL];

    abstract public function getViewPath();

    abstract public function getViewFile();

    abstract public function getViewData();

    abstract public function getLink();

    abstract public function getRegisteredActions();

    abstract public function getAvailableTypesForBlock();

    abstract public function parseConfig($jsonConfig);

    /** @var bool|DbConn */
    protected $db;
    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $order = 0;

    /**
     * @var bool
     */
    private $enabled = false;

    protected $enabledActions = [];

    /** @var string */
    protected $viewPath;

    /** @var string */
    protected $viewFile;

    public function __construct($jsonConfig)
    {
        parent::__construct();
        $this->db = DbConn::getInstance();
        $jsonConfig = json_decode($jsonConfig,true);
        $this->parseBaseConfig($jsonConfig);
        $this->parseConfig($jsonConfig);

        $this->viewPath = strtolower(str_replace('DashboardBlock', '', str_replace('Lms', '', get_class($this))));
        $this->viewFile = strtolower(str_replace('DashboardBlock', '', str_replace('Lms', '', get_class($this))));

    }

    /**
     * @return int
     */
    public function getOrder(){
        return $this->order;
    }

    /**
     * @param int $order
     * @return DashboardBlockLms
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return DashboardBlockLms
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return DashboardBlockLms
     */
    public function setType($type)
    {
        if (!in_array($type, self::ALLOWED_TYPES)) {
            throw new LogicException(sprintf('Selected type is not allowed : %s', $type));
        }
        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    public function getEnabledActions()
    {
        return $this->enabledActions;
    }

    /**
     * @param array $enabledActions
     * @return DashboardBlockLms
     */
    public function setEnabledActions($enabledActions)
    {
        $this->enabledActions = $enabledActions;
        return $this;
    }

    protected function parseBaseConfig($jsonConfig) {
        $this->enabled = $jsonConfig['enabled'] ? $jsonConfig['enabled'] :false;
        $this->type = $jsonConfig['type'] ? $jsonConfig['type'] : '';
        $this->enabledActions = $jsonConfig['enabledActions'] ? $jsonConfig['enabledActions'] : [];
    }

    /**
     * @return array
     */
    public function getCommonViewData()
    {
        return [
            'view' => $this->getViewName(),
            'order' => $this->getOrder(),
            'type' => $this->getType(),
            'availableTypes' => $this->getAvailableTypesForBlock(),
            'enabled' => $this->isEnabled(),
            'link' => $this->getLink(),
            'ajaxUrl' => 'ajax.adm_server.php?r=lms/dashboard/ajaxAction',
            'postData' => [
                'block' => get_class($this),
                'signature' => Util::getSignature()
            ],
            'registeredActions' => $this->getRegisteredActions(),
            'enabledActions' => $this->getEnabledActions(),
            'templatePath' => getPathTemplate()
        ];
    }

    protected function getViewName()
    {
        return sprintf('%s/%s.html.twig', $this->getViewPath(), $this->getViewFile());
    }

    protected function getDataFromCourse($course, $status = true)
    {
        $status_list = [
            0 => Lang::t('_CST_PREPARATION', 'course'),
            1 => Lang::t('_CST_AVAILABLE', 'course'),
            2 => Lang::t('_CST_CONFIRMED', 'course'),
            3 => Lang::t('_CST_CONCLUDED', 'course'),
            4 => Lang::t('_CST_CANCELLED', 'course')
        ];

        $dateBegin = trim(str_replace('00:00:00', '', ($status ? $course['course_date_begin'] : $course['course_date_end'])));
        if ($dateBegin === '0000-00-00') {
            $dateBegin = '';
        } else {
            $startDate = new DateTime($dateBegin);
        }

        $dateEnd = trim(str_replace('00:00:00', '', ($status ? $course['course_date_begin'] : $course['course_date_end'])));
        if ($dateEnd === '0000-00-00') {
            $dateEnd = '';
        } else {
            $endDate = new DateTime($dateEnd);
        }

        $hourBegin = $course['course_hour_begin'];
        $hourBeginString = '';
        if ($hourBegin === '-1' || $hourBegin === null) {
            $hourBegin = '00:00:00';
        } else {
            $hourBegin .= ':00';
            $hourBeginString = $hourBegin;
        }

        $hourEnd = $course['course_hour_end'];
        $hourEndString = '';
        if ($hourEnd === '-1' || $hourEnd === null) {
            $hourEnd = '23:59:59';
        } else {
            $hourEnd .= ':00';
            $hourEndString = $course['course_hour_end'];
        }

        $courseData = [
            'id' => $course['course_id'],
            'title' => $course['course_name'],
            'startDate' => !empty($dateBegin) ? $dateBegin : '',
            'endDate' => !empty($dateEnd) ? $dateEnd : '',
            'startDateString' => !empty($dateBegin) ? $startDate->format('d/m/Y') : '',
            'endDateString' => !empty($dateEnd) ? $endDate->format('d/m/Y') : '',
            'hourBegin' => !empty($dateBegin) ? $hourBegin : '',
            'hourEnd' => !empty($dateEnd) ? $hourEnd : '',
            'type' => $course['course_type'],
            'status' => $status,
            'nameCategory' => $this->getCategory($course['course_category_id']),
            'courseStatus' => $course['course_status'],
            'courseStatusString' => $status_list[(int)$course['course_status']],
            'description' => $course['course_box_description'],
            'img' => (!empty($course['course_img_course']) ? Get::site_url() . _folder_files_ . '/' . _folder_lms_ . '/' . Get::sett('pathcourse') . $course['course_img_course'] : ''),
            'hours' => $hourBeginString . (!empty($hourEndString) ? ' ' . $hourEndString : ''),
            'dates' => []
        ];

        return $courseData;
    }

    protected function getDataFromReservation($reservation)
    {
        $dateBegin = $reservation['date_begin'];
        if ($dateBegin === '0000-00-00') {
            $dateBegin = '';
        }

        $dateEnd = $reservation['date_end'];
        if ($dateEnd === '0000-00-00') {
            $dateEnd = '';
        }

        $hourBegin = $reservation['hour_begin'];
        $hourBeginString = '';
        if ($hourBegin === '-1') {
            $hourBegin = '00:00:00';
        } else {
            $hourBegin .= ':00';
            $hourBeginString = $reservation['hour_begin'];
        }
        $hourEnd = $reservation['hour_end'];
        $hourEndString = '';
        if ($hourEnd === '-1') {
            $hourEnd = '23:59:59';
        } else {
            $hourEnd .= ':00';
            $hourEndString = $reservation['hour_end'];
        }

        $reservationData = [
            'title' => $reservation['name'],
            'start' => $dateBegin . 'T' . $hourBegin,
            'end' => $dateEnd . 'T' . $hourEnd,
            'type' => $reservation['course_type'],
            'status' => true,
            'description' => $reservation['box_description'],
            'hours' => $hourBeginString . ' ' . $hourEndString,
        ];

        $reservationData['course'] = $this->getCalendarDataFromCourse($reservation);

        return $reservationData;
    }

    protected function getCategory($idCat)
    {
        $query = "select path from %lms_category where idCategory=" . $idCat;
        $res = $this->db->query($query);
        $path = "";
        if ($res && $this->db->num_rows($res) > 0) {
            list($path) = $this->db->fetch_row($res);
        }
        return $path;
    }
}
