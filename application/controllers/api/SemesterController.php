<?php

include APPPATH . 'libraries/REST_Controller.php';

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST");

class SemesterController extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/SemesterModel');
        $this->load->helper(array('authorization','jwt'));
        $this->load->library('form_validation');
    }

    // Controller
    public function create_project_post()
    {
        // Get the Authorization header
        $token = $this->input->get_request_header('Authorization');
        $data = json_decode($this->input->raw_input_stream);

        // Check if the token is provided
        if (!$token) {
            $this->response(['status' => 'failed', 'message' => 'Authorization header missing'], parent::HTTP_UNAUTHORIZED);
            return;
        }
       
        // Validate the token format and expiry
        try {
            
            $student_data = authorization::validateToken($token);
           // print_r($token);die();
           
            if ($student_data !== false) {
                
                // Retrieve project data from request
                $project_data = [
                    'student_id' => $student_data->data->id,
                    'title' => $data->title,
                    'level' => $data->level,
                    'description' => $data->description,
                    'complete_days' => $data->complete_days,
                    'semester' => $data->semester,
                ];

                // Validate project data
                $this->form_validation->set_data($project_data);
                $this->form_validation->set_rules('title', 'Title', 'required|max_length[255]');
                $this->form_validation->set_rules('level', 'Level', 'required|integer');
                $this->form_validation->set_rules('description', 'Description', 'required');
                $this->form_validation->set_rules('complete_days', 'Complete Days', 'required|integer');
                $this->form_validation->set_rules('semester', 'Semester', 'required|max_length[255]');

                if ($this->form_validation->run() == false) {
                    $this->response(['status' => 'failed', 'message' => validation_errors()], parent::HTTP_BAD_REQUEST);
                    return;
                }

                // Insert project data into database
                if ($this->SemesterModel->create_project($project_data)) {
                    // Log the project creation activity
                    $this->logActivity('Project created by user: ' . $student_data->data->id);

                    $this->response(['status' => 'success', 'message' => 'Project created successfully'], parent::HTTP_OK);
                } else {
                    $this->response(['status' => 'failed', 'message' => 'Failed to create project'], parent::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                // Invalid token
                $this->response(['status' => 'failed', 'message' => 'Invalid Token1'], parent::HTTP_UNAUTHORIZED);
            }
        } catch (\Throwable $th) {
            // Error occurred while validating token
            $this->response(['status' => 'failed', 'message' => 'Invalid Token1'], parent::HTTP_UNAUTHORIZED);
        }
    }


    private function logActivity($message)
    {
        // Define the path to the log file
        $logFilePath = APPPATH . 'logs/project_activity.log';

        // Get the current timestamp
        $timestamp = date('Y-m-d H:i:s');

        // Format the log message
        $logMessage = "[$timestamp] $message" . PHP_EOL;

        // Append the log message to the log file
        if (file_exists($logFilePath) && is_writable($logFilePath)) {
            file_put_contents($logFilePath, $logMessage, FILE_APPEND | LOCK_EX);
        } else {
            // Handle file write error
            error_log("Failed to write to log file: $logFilePath");
        }
    }
}
