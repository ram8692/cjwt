<?php

class BranchModel extends CI_Model {
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function create($data){

       return $this->db->insert('tbl_branches', $data);
    }

    public function get_all_branch($filters = array())
    {
        // Start building the query
        $this->db->select('*')->from('tbl_branches');

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
        $this->db->delete('tbl_branches');
    
        // Check if the deletion was successful
        if ($this->db->affected_rows() > 0) {
            return true; // Deletion successful
        } else {
            return false; // Deletion failed
        }
    }

}



?>