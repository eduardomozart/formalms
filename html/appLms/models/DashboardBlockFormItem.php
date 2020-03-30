<?php


class DashboardBlockFormItem
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $class;

    /** @var string */
    protected $field;

    /** @var bool */
    protected $required;

    /** @var string */
    protected $type;

    /** @var array */
    protected $values;

    /** @var array */
    protected $attr;

    public function __construct($name, $class, $required, $type, $values, $attr)
    {
        $this->name = $name;
        $this->class = $class;
        $this->field = sprintf('%s-%s', $class, $name);
        $this->required = $required;
        $this->type = $type;
        $this->values = $values;
        $this->attr = $attr;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return array
     */
    public function getAttr()
    {
        return $this->attr;
    }
}