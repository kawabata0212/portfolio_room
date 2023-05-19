<?php

abstract class DbRepository
{
    protected $con;

    public function __construct($con)
    {
        $this->setConnection($con);
    }

    public function setConnection($con)
    {
        $this->con = $con;
    }

    public function execute($sql, $params = array())
    {
        $stmt = $this->con->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    public function fetch($sql, $params = array())
    {
        return $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchAll($sql, $params = array())
    {
        return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
    * BEGINするだけ。
    */
    public function begin()
    {
        $sql = "BEGIN";         
        return $this->fetchAll($sql,);
    }


    /**
    * COMMITするだけ。
    */
    public function commit()
    {
        $sql = "COMMIT";            
        return $this->fetchAll($sql,);
    }


    /**
    *  ★sleep 完全検証用
    */
    public function sleep($second)
    {
        $sql = "SELECT sleep($second)";
        return $this->fetchAll($sql,);
    }

}
