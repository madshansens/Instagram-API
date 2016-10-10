<?php

namespace InstagramAPI;

class Experiment
{
    protected $params;
    protected $group;
    protected $name;

    public function __construct($data)
    {
        $params = [];
        foreach ($data['params'] as $param) {
            $params[] = new Param($param);
        }
        $this->params = $params;
        $this->group = $data['group'];
        $this->name = $data['name'];
    }

    /**
     * @return Param[]
     */
    public function getParams()
    {
        return $this->params;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function getName()
    {
        return $this->name;
    }
}
