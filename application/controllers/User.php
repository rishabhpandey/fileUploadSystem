<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : User (UserController)
 * User Class to control all user related operations.
 */
class User extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->isLoggedIn();
    }

    /**
     * This function used to load the first screen of the user
     */
    public function index()
    {
        $this->global['pageTitle'] = 'File Upload : Dashboard';

        $this->loadViews("dashboard", $this->global, null, null);
    }

    /**
     * This function is used to load the user list
     */
    public function userListing()
    {
        if ($this->isAdmin() == true) {
            $this->loadThis();
        } else {
            $this->load->model('User_model');

            $searchText         = $this->input->post('searchText');
            $data['searchText'] = $searchText;

            $this->load->library('pagination');

            $count = $this->User_model->userListingCount($searchText);

            $returns = $this->paginationCompress("userListing/", $count, 5);

            $data['userRecords'] = $this->User_model->userListing($searchText, $returns["page"], $returns["segment"]);

            $this->global['pageTitle'] = 'File Upload : User Listing';

            $this->loadViews("users", $this->global, $data, null);
        }
    }

    /**
     * This function is used to load the add new form
     */
    public function addNew()
    {
        if ($this->isAdmin() == true) {
            $this->loadThis();
        } else {
            $this->load->model('User_model');
            $data['roles'] = $this->User_model->getUserRoles();

            $this->global['pageTitle'] = 'File Upload : Add New User';

            $this->loadViews("addNew", $this->global, $data, null);
        }
    }

    /**
     * This function is used to check whether email already exist or not
     */
    public function checkEmailExists()
    {
        $userId = $this->input->post("userId");
        $email  = $this->input->post("email");

        if (empty($userId)) {
            $result = $this->User_model->checkEmailExists($email);
        } else {
            $result = $this->User_model->checkEmailExists($email, $userId);
        }

        if (empty($result)) {echo ("true");} else {echo ("false");}
    }

    /**
     * This function is used to add new user to the system
     */
    public function addNewUser()
    {
        if ($this->isAdmin() == true) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('fname', 'Full Name', 'trim|required|max_length[128]|xss_clean');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|xss_clean|max_length[128]');
            $this->form_validation->set_rules('password', 'Password', 'required|max_length[20]');
            $this->form_validation->set_rules('cpassword', 'Confirm Password', 'trim|required|matches[password]|max_length[20]');
            $this->form_validation->set_rules('role', 'Role', 'trim|required|numeric');
            $this->form_validation->set_rules('mobile', 'Mobile Number', 'required|min_length[10]|xss_clean');

            if ($this->form_validation->run() == false) {
                $this->addNew();
            } else {
                $name     = ucwords(strtolower($this->input->post('fname')));
                $email    = $this->input->post('email');
                $password = $this->input->post('password');
                $roleId   = $this->input->post('role');
                $mobile   = $this->input->post('mobile');

                $userInfo = array('email' => $email, 'password'   => getHashedPassword($password), 'roleId' => $roleId, 'name' => $name,
                    'mobile'                  => $mobile, 'createdBy' => $this->vendorId, 'created_at'          => date('Y-m-d H:i:s'));

                $this->load->model('User_model');
                $result = $this->User_model->addNewUser($userInfo);

                if ($result > 0) {
                    $this->session->set_flashdata('success', 'New User created successfully');
                } else {
                    $this->session->set_flashdata('error', 'User creation failed');
                }

                redirect('addNew');
            }
        }
    }

    /**
     * This function is used load user edit information
     * @param number $userId : Optional : This is user id
     */
    public function editOld($userId = null)
    {
        if ($this->isAdmin() == true || $userId == 1) {
            $this->loadThis();
        } else {
            if ($userId == null) {
                redirect('userListing');
            }

            $data['roles']    = $this->User_model->getUserRoles();
            $data['userInfo'] = $this->User_model->getUserInfo($userId);

            $this->global['pageTitle'] = 'File Upload : Edit User';

            $this->loadViews("editOld", $this->global, $data, null);
        }
    }

    /**
     * This function is used to edit the user information
     */
    public function editUser()
    {
        if ($this->isAdmin() == true) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $userId = $this->input->post('userId');

            $this->form_validation->set_rules('fname', 'Full Name', 'trim|required|max_length[128]|xss_clean');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|xss_clean|max_length[128]');
            $this->form_validation->set_rules('password', 'Password', 'matches[cpassword]|max_length[20]');
            $this->form_validation->set_rules('cpassword', 'Confirm Password', 'matches[password]|max_length[20]');
            $this->form_validation->set_rules('role', 'Role', 'trim|required|numeric');
            $this->form_validation->set_rules('mobile', 'Mobile Number', 'required|min_length[10]|xss_clean');

            if ($this->form_validation->run() == false) {
                $this->editOld($userId);
            } else {
                $name     = ucwords(strtolower($this->input->post('fname')));
                $email    = $this->input->post('email');
                $password = $this->input->post('password');
                $roleId   = $this->input->post('role');
                $mobile   = $this->input->post('mobile');

                $userInfo = array();

                if (empty($password)) {
                    $userInfo = array('email' => $email, 'roleId'     => $roleId, 'name'               => $name,
                        'mobile'                  => $mobile, 'updatedBy' => $this->vendorId, 'updatedDtm' => date('Y-m-d H:i:s'));
                } else {
                    $userInfo = array('email' => $email, 'password'       => getHashedPassword($password), 'roleId' => $roleId,
                        'name'                    => ucwords($name), 'mobile' => $mobile, 'updatedBy'                   => $this->vendorId,
                        'updatedDtm'              => date('Y-m-d H:i:s'));
                }

                $result = $this->User_model->editUser($userInfo, $userId);

                if ($result == true) {
                    $this->session->set_flashdata('success', 'User updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'User updation failed');
                }

                redirect('userListing');
            }
        }
    }

    /**
     * This function is used to delete the user using userId
     * @return boolean $result : TRUE / FALSE
     */
    public function deleteUser()
    {
        if ($this->isAdmin() == true) {
            echo (json_encode(array('status' => 'access')));
        } else {
            $userId   = $this->input->post('userId');
            $userInfo = array('isDeleted' => 1, 'updatedBy' => $this->vendorId, 'updatedDtm' => date('Y-m-d H:i:s'));

            $result = $this->User_model->deleteUser($userId, $userInfo);

            if ($result > 0) {echo (json_encode(array('status' => true)));} else {echo (json_encode(array('status' => false)));}
        }
    }

    /**
     * This function is used to load the change password screen
     */
    public function loadChangePass()
    {
        $this->global['pageTitle'] = 'File Upload : Change Password';

        $this->loadViews("changePassword", $this->global, null, null);
    }

    /**
     * This function is used to change the password of the user
     */
    public function changePassword()
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('oldPassword', 'Old password', 'required|max_length[20]');
        $this->form_validation->set_rules('newPassword', 'New password', 'required|max_length[20]');
        $this->form_validation->set_rules('cNewPassword', 'Confirm new password', 'required|matches[newPassword]|max_length[20]');

        if ($this->form_validation->run() == false) {
            $this->loadChangePass();
        } else {
            $oldPassword = $this->input->post('oldPassword');
            $newPassword = $this->input->post('newPassword');

            $resultPas = $this->User_model->matchOldPassword($this->vendorId, $oldPassword);

            if (empty($resultPas)) {
                $this->session->set_flashdata('nomatch', 'Your old password not correct');
                redirect('loadChangePass');
            } else {
                $usersData = array('password' => getHashedPassword($newPassword), 'updatedBy' => $this->vendorId,
                    'updatedDtm'                  => date('Y-m-d H:i:s'));

                $result = $this->User_model->changePassword($this->vendorId, $usersData);

                if ($result > 0) {$this->session->set_flashdata('success', 'Password updation successful');} else { $this->session->set_flashdata('error', 'Password updation failed');}

                redirect('loadChangePass');
            }
        }
    }

    public function fileUpload()
    {
        if ($this->isAdmin() == true) {
            $this->loadThis();
        } else {
            $this->load->model('User_model');

            $this->global['pageTitle'] = 'File Upload : Add New File';

            $this->loadViews("fileUpload", $this->global, null);
        }
    }

    public function pageNotFound()
    {
        $this->global['pageTitle'] = 'File Upload : 404 - Page Not Found';

        $this->loadViews("404", $this->global, null, null);
    }

    public function submitFileUpload()
    {

        $folderName    = substr($_FILES['userFile']['name'], 0, strpos($_FILES['userFile']['name'], '_'));
        $upload_config = array('upload_path' => 'uploads/' . $folderName . '/', 'allowed_types' =>
            'jpg|jpeg|gif|png|pdf', 'max_size' => '2000');
        $this->load->library('upload', $upload_config);

        // create an album if not already exist in uploads dir
        // wouldn't make more sence if this part is done if there are no errors and right before the upload ??
        if (!is_dir('uploads/' . $folderName)) {
            //echo "string";
            @mkdir('uploads/' . $folderName, 0777, true);
        }
        $dir_exist = true; // flag for checking the directory exist or not

        if (!is_dir('uploads/' . $folderName)) {
            @mkdir('uploads/' . $folderName, 0777, true);
            $dir_exist = false; // dir not exist
        }

        clearstatcache();
        if (!$this->upload->do_upload('userFile')) {
            // upload failed
            //delete dir if not exist before upload
            if (!$dir_exist) {
                rmdir('uploads/' . $folderName);
            }

            echo ($this->upload->display_errors('<span>', '</span>'));
            return array('error' => $this->upload->display_errors('<span>', '</span>'));
        } else {
            echo "File Uploaded Successfully!!";

            // upload success
            $upload_data = $this->upload->data();
            //return true;
        }

        $data = array('upload_data' => $this->upload->data());
        $this->session->set_flashdata('success', 'Uploaded Successfully');
        return redirect('user/fileUpload');
    }

    public function filesListing()
    {
        if ($this->isAdmin() == true) {
            $this->loadThis();
        } else {

            $this->load->model('User_model');

            $userData = $this->User_model->userListing();
            foreach ($userData as $user) {
                $fileData[$user->userId] = $this->getUserFiles($user->userId);
            }

            $searchText         = $this->input->post('searchText');
            $data['searchText'] = $searchText;

            $this->load->library('pagination');

            $count = $this->User_model->filesListingCount($searchText);

            $returns = $this->paginationCompress("filesListing/", $count, 5);
            $dir     = "/images/";

            // Open a directory, and read its contents
            if (is_dir($dir)) {
                if ($dh = opendir($dir)) {
                    while (($file = readdir($dh)) !== false) {
                        echo "filename:" . $file . "<br>";
                    }
                    closedir($dh);
                }
            }

            $data['userRecords'] = $this->User_model->userListing($searchText, $returns["page"], $returns["segment"]);

            //$data['thelist'] = $this->User_model->filesListing($searchText, $returns["page"], $returns["segment"]);

            $data['thelist'] = $fileData;

            $this->global['pageTitle'] = 'File Upload : Files Listing';

            $this->loadViews("files", $this->global, $data, null);
        }
    }

    public function getUserFiles($id)
    {
        $thelist = '';
        if ($handle = opendir('uploads/' . $id)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $thelist .= $file . ',';
                }
            }
            closedir($handle);
        }
        return rtrim($thelist, ',');

    }

}
