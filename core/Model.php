<?php

class Model
{
    public PDO $db;

    public function __construct()
    {
        $this->db =
            Database::getInstance();
    }
}