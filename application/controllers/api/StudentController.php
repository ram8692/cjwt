<?php

include APPPATH . 'libraries/REST_Controller.php';

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST");

class StudentController extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/StudentModel');
        $this->load->helper(array('jwt', 'authorization'));
        $this->load->library('form_validation');
    }

    public function index_get()
    {
        $student_list = $this->StudentModel->get_all_students();

        if (!empty($student_list)) {
            $this->response(['status' => 'success', 'message' => 'studentes found', 'data' => $student_list], parent::HTTP_OK);
        } else {
            $this->response(['status' => 'failed', 'message' => 'No studentes found'], parent::HTTP_NOT_FOUND);
        }
    }

    public function register_post()
    {
        // Decode the JSON data from the request
        $data = json_decode($this->input->raw_input_stream);

        //print_r($data);die();

        // Check if the JSON data is valid
        if (!$data || !isset($data->name, $data->email, $data->branch_id, $data->status, $data->gender, $data->password)) {
            $this->response(['status' => 'failed', 'message' => 'Invalid or missing JSON data'], parent::HTTP_BAD_REQUEST);
            return;
        }

        // Check if email already exists
        if (!$this->StudentModel->is_email_exist($data->email)) {
            $this->response(['status' => 'failed', 'message' => 'Email already exists'], parent::HTTP_BAD_REQUEST);
            return;
        }

        // Set validation rules
        $validation_rules = [
            ['field' => 'name', 'label' => 'Name', 'rules' => 'required|trim|max_length[255]'],
            ['field' => 'email', 'label' => 'Email', 'rules' => 'required|valid_email|trim|max_length[255]'],
            ['field' => 'branch_id', 'label' => 'Branch ID', 'rules' => 'required|integer'],
            ['field' => 'status', 'label' => 'Status', 'rules' => 'required|trim|max_length[255]'],
            ['field' => 'gender', 'label' => 'Gender', 'rules' => 'required|in_list[male,female]'],
            ['field' => 'password', 'label' => 'Password', 'rules' => 'required|min_length[6]|max_length[255]']
        ];

        // Check if the phone field is provided, if yes, add validation rule
        if (isset($data->phone)) {
            $validation_rules[] = ['field' => 'phone', 'label' => 'Phone', 'rules' => 'trim|numeric'];
        }

        // Set validation rules
        $this->form_validation->set_data((array) $data)->set_rules($validation_rules);

        // Run validation
        if ($this->form_validation->run() == FALSE) {
            $this->response(['status' => 'failed', 'message' => validation_errors()], parent::HTTP_BAD_REQUEST);
            return;
        }

        // Prepare data for insertion
        $student_data = [
            'name' => $data->name,
            'email' => $data->email,
            'branch_id' => $data->branch_id,
            'status' => $data->status,
            'gender' => $data->gender,
            'password' => password_hash($data->password, PASSWORD_DEFAULT), // Hash the password
            'phone' => isset($data->phone) ? $data->phone : null // Set phone value or null if not provided
        ];

        // Insert the student data into the database using the StudentModel
        if ($this->StudentModel->register($student_data)) {
            $this->response(['status' => 'success', 'message' => 'Student registered successfully'], parent::HTTP_OK);
        } else {
            $this->response(['status' => 'failed', 'message' => 'Failed to register student'], parent::HTTP_INTERNAL_SERVER_ERROR);
        }
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
        if ($this->StudentModel->delete($data->id)) {
            $this->response(['status' => 'success', 'message' => 'Student deleted successfully'], parent::HTTP_OK);
        } else {
            $this->response(['status' => 'failed', 'message' => 'Failed to delete branch'], parent::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update_put()
    {
        // Decode the JSON data from the request
        $data = json_decode($this->input->raw_input_stream);

        // Check if the JSON data is valid and contains the student ID
        if (!$data || !isset($data->id, $data->name, $data->email, $data->branch_id, $data->status, $data->gender)) {
            $this->response(['status' => 'failed', 'message' => 'Invalid or missing JSON data'], parent::HTTP_BAD_REQUEST);
            return;
        }

        // Check if the student ID is numeric
        if (!is_numeric($data->id)) {
            $this->response(['status' => 'failed', 'message' => 'Invalid student ID'], parent::HTTP_BAD_REQUEST);
            return;
        }

        // Check if the student exists
        $existing_student = $this->StudentModel->get_student_by_id($data->id);
        if (!$existing_student) {
            $this->response(['status' => 'failed', 'message' => 'Student not found'], parent::HTTP_NOT_FOUND);
            return;
        }

        // Set validation rules
        $validation_rules = [
            ['field' => 'name', 'label' => 'Name', 'rules' => 'required|trim|max_length[255]'],
            ['field' => 'email', 'label' => 'Email', 'rules' => 'required|valid_email|trim|max_length[255]'],
            ['field' => 'branch_id', 'label' => 'Branch ID', 'rules' => 'required|integer'],
            ['field' => 'status', 'label' => 'Status', 'rules' => 'required|trim|max_length[255]'],
            ['field' => 'gender', 'label' => 'Gender', 'rules' => 'required|in_list[male,female]']
        ];

        // Set validation rules
        $this->form_validation->set_data([
            'name' => $data->name,
            'email' => $data->email,
            'branch_id' => $data->branch_id,
            'status' => $data->status,
            'gender' => $data->gender
        ])->set_rules($validation_rules);

        // Run validation
        if ($this->form_validation->run() == FALSE) {
            $this->response(['status' => 'failed', 'message' => validation_errors()], parent::HTTP_BAD_REQUEST);
            return;
        }

        // Prepare data for update
        $student_data = [
            'name' => $data->name,
            'email' => $data->email,
            'branch_id' => $data->branch_id,
            'status' => $data->status,
            'gender' => $data->gender
        ];

        // Perform the update using the StudentModel
        if ($this->StudentModel->update_student($data->id, $student_data)) {
            $this->response(['status' => 'success', 'message' => 'Student updated successfully'], parent::HTTP_OK);
        } else {
            $this->response(['status' => 'failed', 'message' => 'Failed to update student'], parent::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function login_post()
    {
        // Decode the JSON data from the request
        $data = json_decode($this->input->raw_input_stream);

        // Set validation rules
        $this->form_validation->set_data((array) $data);
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');

        // Run validation
        if ($this->form_validation->run() == FALSE) {
            $this->response(['status' => 'failed', 'message' => validation_errors()], parent::HTTP_BAD_REQUEST);
            return;
        }

        $email = $data->email;
        $password = $data->password;

        $studentDetails = $this->StudentModel->is_email_exist($email);

        if (empty($studentDetails)) {
            $this->response(['status' => 'failed', 'message' => 'Email Not Found'], parent::HTTP_NOT_FOUND);
            return;
        }

        if (!password_verify($password, $studentDetails->password)) {
            $this->response(['status' => 'failed', 'message' => 'Incorrect Password'], parent::HTTP_BAD_REQUEST);
            return;
        }

        // Generate token and send response
        $token = authorization::generateToken((array)$studentDetails);
        $this->response(['status' => 'success', 'message' => 'Login Successful', 'token' => $token], parent::HTTP_OK);
    }

    public function student_details_get()
{
    // Get the Authorization header
    $token = $this->input->get_request_header('Authorization');

    // Check if the token is provided
    if (!$token) {
        $this->response(['status' => 'failed', 'message' => 'Authorization header missing'], parent::HTTP_UNAUTHORIZED);
        return;
    }

    // Validate the token
    try {
        $student_data = authorization::validateToken($token);
        if ($student_data !== false) {
            // Token is valid, send student details in the response
            $this->response(['status' => 'success', 'message' => 'Student Details', 'data' => $student_data], parent::HTTP_OK);
        } else {
            // Invalid token
            $this->response(['status' => 'failed', 'message' => 'Invalid Token'], parent::HTTP_UNAUTHORIZED);
        }
    } catch (\Throwable $th) {
        // Error occurred while validating token
        $this->response(['status' => 'failed', 'message' => 'Invalid Token'], parent::HTTP_UNAUTHORIZED);
    }
}



}
