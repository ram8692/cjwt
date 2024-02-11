<?php

class SemesterModel extends CI_Model {
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function create_project($data)
    {
        return $this->db->insert('tbl_semester_projects', $data);
    }


}



?>