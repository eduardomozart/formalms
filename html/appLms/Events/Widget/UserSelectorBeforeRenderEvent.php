<?php
namespace appLms\Events\Widget;

use Symfony\Component\EventDispatcher\Event;

class UserSelectorBeforeRenderEvent extends Event
{
    const EVENT_NAME = 'widget.user_selector.before_render';
    protected $userSelectorId;
    protected $columns;
    protected $fields;

    public function __construct($userSelectorId, $columns = array(), $fields = array())
    {
        $this->userSelectorId = $userSelectorId;
        $this->columns = $columns;
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param array $column
     */
    public function addColumn($column, $position = null)
    {
        if ($position == null) {
            $this->columns[] = $column;
            return;
        }
        $head = array_slice($this->columns, 0, $position);
        $tail = array_slice($this->columns, $position);
        $this->columns = array_merge($head, array($column));
        $this->columns = array_merge($this->columns, $tail);

    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $field
     */
    public function addField($field, $position = null)
    {
        if ($position == null) {
            $this->fields[] = $field;
            return;
        }
        $head = array_slice($this->fields, 0, $position -1);
        $tail = array_slice($this->fields, $position);
        $this->fields = array_merge($head, $field);
        $this->fields = array_merge($this->fields, $tail);
    }

    /**
     * @return mixed
     */
    public function getUserSelectorId()
    {
        return $this->userSelectorId;
    }

}