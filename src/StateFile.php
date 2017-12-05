<?php

namespace Bhusalb;


class StateFile
{

    protected $data;
    protected $path;

    public function __construct($path)
    {

        $this->path = $path;

        if (is_file($this->path)) {
            $this->data = json_decode(file_get_contents($this->path),true);
            if (!(json_last_error() === JSON_ERROR_NONE))
                throw new InvalidStateFileException();
        } else
            throw new StateFileMissingException();
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function overwrite($data)
    {
        file_put_contents($this->path, json_encode($data));
    }
}