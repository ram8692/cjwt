<?php

include APPPATH.'libraries/REST_Controller.php';

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST");

class BranchController extends REST_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('api/BranchModel');
    }

    public function index_get(){

    }

    public function create_post(){

    }

    public function update_post(){
    }

    public function delete_post(){

    }

    



}

?>