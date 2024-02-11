<?php

class StudentModel extends CI_Model {
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function register($data) {
        // Insert student data into the database
        return $this->db->insert('tbl_students', $data);
    }

    public function is_email_exist($email) {
        // Check if the email exists in the database
        $this->db->where('email', $email);
       return $query = $this->db->get('tbl_students')->row();
    
        // If there is no record with the given email, return true (unique)
       // return $query->num_rows() == 0;
    }

    public function get_all_students($filters = array())
    {
        // Start building the query
        $this->db->select('*')->from('tbl_students');

        // Apply filters
        if (!empty($filters)) {
            if (isset($filters['name'])) {
                $this->db->like('name', $filters['name']);
            }
            // Add more filters as needed
        }

        //desc by id
        $this->db->order_by('id', 'DESC');

        // Execute the query
        $query = $this->db->get();

        // Return the result
        return $query->result();
    }

    public function delete($id) {
        // Check if the ID is provided and is numeric
        if (!is_numeric($id)) {
            return false; // Invalid ID
        }
    
        // Perform the deletion
        $this->db->where('id', $id);
        $this->db->delete('tbl_students');
    
        // Check if the deletion was successful
        if ($this->db->affected_rows() > 0) {
            return true; // Deletion successful
        } else {
            return false; // Deletion failed
        }
    }

    public function update_student($id, $data)
    {
        // Check if the provided student ID exists
        $existing_student = $this->get_student_by_id($id);
        if (!$existing_student) {
            return false; // Student not found
        }

        // Update the student record
        $this->db->where('id', $id);
        return $this->db->update('tbl_students', $data);
    }

    public function get_student_by_id($id)
    {
        return $this->db->get_where('tbl_students', ['id' => $id])->row();
    }


}



?>