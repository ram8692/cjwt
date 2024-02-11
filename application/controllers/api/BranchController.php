<?php

require APPPATH . 'libraries/REST_Controller.php';

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST");

class BranchController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/BranchModel'); // Adjust the path to match your directory structure
        $this->load->library('form_validation');
    }



    public function index_get()
    {
        $branch_list = $this->BranchModel->get_all_branch();
    
        if (!empty($branch_list)) {
            $this->response(['status' => 'success', 'message' => 'branches found', 'data' => $branch_list], parent::HTTP_OK);
        } else {
            $this->response(['status' => 'failed', 'message' => 'No branches found'], parent::HTTP_NOT_FOUND);
        }
    }
    

    public function create_post()
    {
        //$data = json_decode(file_get_contents("php://input"));
        $data = json_decode($this->input->raw_input_stream);
    
        $this->form_validation->set_data(['name' => $data->name])->set_rules('name', 'Name', 'required|trim|max_length[255]|string');
    
        if ($this->form_validation->run() == FALSE) {
            $this->response(['status' => 'failed', 'message' => $this->form_validation->error_array()], parent::HTTP_BAD_REQUEST);
            return;
        }
    
        $branch_data = ['name' => $data->name];
    
        if ($this->BranchModel->create($branch_data)) {
            $this->response(['status' => 'success', 'message' => 'Branch created successfully'], parent::HTTP_OK);
        } else {
            $this->response(['status' => 'failed', 'message' => 'Failed to create branch'], parent::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    public function update_post()
    {
        // Implementation for updating a branch can be added here if needed
    }

    public function delete_delete()
    {
        // Decode the JSON data from the request
        $data = json_decode($this->input->raw_input_stream);

        // Check if the JSON data is valid and contains the 'id' field
        if (!isset($data->id) || !is_numeric($data->id)) {
            $this->response(['status' => 'failed', 'message' => 'Invalid or missing ID'], parent::HTTP_BAD_REQUEST);
            return;
        }

        // Attempt to delete the branch
        if ($this->BranchModel->delete($data->id)) {
            $this->response(['status' => 'success', 'message' => 'Branch deleted successfully'], parent::HTTP_OK);
        } else {
            $this->response(['status' => 'failed', 'message' => 'Failed to delete branch'], parent::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
