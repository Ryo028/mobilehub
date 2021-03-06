<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rest
 *
 * @author DRX
 */
class Api extends CI_Controller {

    /**
     * Constructor of the REST API
     */
    function __construct() {
        parent::__construct();
        $this->load->library(array('authlib', 'searchlib', 'questionslib', 'voteslib', 'permlib', 'userlib', 'adminlib'));

        $this->ci = &get_instance();
        $this->ci->load->model('user');
    }

    /**
     * Mapping the GET and POST requests
     */
    public function _remap() {
        $request_method = $this->input->server('REQUEST_METHOD');
        switch (strtolower($request_method)) {
            case 'post' : $this->post();
                break;
            case 'get' : $this->get();
                break;
            default:
                show_error('Unsupported method', 404); // CI function for 404 errors
                break;
        }
    }

    /**
     * Mapping of the GET method from the REST Controller
     */
    private function get() {
        $args = $this->uri->uri_to_assoc(1);
        switch (strtolower($args['api'])) {
            case 'logout' :
                $this->logout();
                break;
            case 'search':
                $this->loadSearchLogic($args);
                break;
            case 'forgot':
                $this->forgot();
                break;
            case 'tags':
                $this->loadTagsLogic($args);
                break;
            case 'question':
                $this->loadQuestionLogic($args);
                break;
            case 'user':
                $this->loadProfileLogic($args);
                break;
            case 'category':
                $this->loadCategoryLogic($args);
                break;
            default:
                show_error('Unsupported resource', 404);
                break;
        }
    }

    /**
     * Mapping the POST request from a client
     */
    private function post() {
        $args = $this->uri->uri_to_assoc(1);
        switch ($args['api']) {
            case 'auth' :
                $this->loadAuthLogic($args);
                break;
            case 'search':
                $this->loadSearchLogic($args);
                break;
            case 'question':
                $this->loadQuestionLogic($args);
                break;
            case 'vote':
                $this->loadVoteLogic($args);
                break;
            case 'answer':
                $this->loadAnswerLogic($args);
                break;
            case 'user':
                $this->loadProfileLogic($args);
                break;
            case 'admin':
                $this->loadAdminLogic($args);
                break;
            default:
                show_error('Unsupported resource', 404);
                break;
        }
    }

    /**
     * All the REST methods related to authentication logic
     * @param type $args
     */
    private function loadAuthLogic($args) {
        if (array_key_exists('login', $args)) {
            $this->authenticate();
        } else if (array_key_exists('create', $args)) {
            $this->createaccount();
        } else if (array_key_exists('logout', $args)) {
            // not sure yet
        } else if (array_key_exists('forgot', $args)) {
            $this->forgotPass();
        } else if (array_key_exists('reset', $args)) {
            $this->resetPass();
        }
    }

    /**
     * All the REST methods related to search logic
     * @param type $args
     */
    private function loadSearchLogic($args) {
        if (in_array('advanced', $args)) {
            $this->advSearchQuestions();
        } else if (array_key_exists('questions', $args)) {
            $this->searchQuestions();
        }
    }

    /**
     * All the REST methods related to question logic
     * @param type $args
     */
    private function loadQuestionLogic($args) {
        if (array_key_exists('post', $args)) {
            $this->postQuestion();
        } else if (array_key_exists('details', $args)) {
            $this->getDetails($args);
        } else if (array_key_exists('recent', $args)) {
            $this->getRecent($args['recent']);
        } else if (array_key_exists('popular', $args)) {
            $this->getPopular($args['popular']);
        } else if (array_key_exists('unanswered', $args)) {
            $this->getUnanswered($args['unanswered']);
        } else if (array_key_exists('all', $args)) {
            $this->getAll($args['all']);
        } else if (array_key_exists('delete', $args)) {
            $this->deleteQuestion();
        } else if (array_key_exists('update', $args)) {
            $this->updateQuestion($args);
        } else if (array_key_exists('close', $args)) {
            $this->closeQuestion();
        } else if (array_key_exists('flag', $args)) {
            $this->flagQuestion();
        }
    }

    /**
     * All the REST methods related to tags logic
     * @param type $args
     */
    private function loadTagsLogic($args) {
        if (array_key_exists('all', $args)) {
            $this->getAllTags();
        } else if (array_key_exists('recent', $args)) {
            $this->getRecentTags($args['recent'], str_replace("+", " ", $args['tag']));
        } else if (array_key_exists('popular', $args)) {
            $this->getPopularTags($args['popular'], str_replace("+", " ", $args['tag']));
        } else if (array_key_exists('unanswered', $args)) {
            $this->getUnansweredTags($args['unanswered'], str_replace("+", " ", $args['tag']));
        } else if (array_key_exists('alltags', $args)) {
            $this->getAllTagsForTag($args['alltags'], str_replace("+", " ", $args['tag']));
        }
    }

    /**
     * All the REST methods related to categories logic
     * @param type $args
     */
    private function loadCategoryLogic($args) {
        if (array_key_exists('recent', $args)) {
            $this->getRecentCat($args['recent'], str_replace("+", " ", $args['category']));
        } else if (array_key_exists('popular', $args)) {
            $this->getPopularCat($args['popular'], str_replace("+", " ", $args['category']));
        } else if (array_key_exists('unanswered', $args)) {
            $this->getUnansweredCat($args['unanswered'], str_replace("+", " ", $args['category']));
        }
    }

    /**
     * All the REST methods related to voting logic
     * @param type $args
     * @return string
     */
    private function loadVoteLogic($args) {
        if (array_key_exists('voteup', $args)) {
            $this->voteUp($args['voteup']);
        } else if (array_key_exists('votedown', $args)) {
            $this->voteDown($args['votedown']);
        } else {
            $response['message'] = 'Error';
            return $response;
        }
    }

    /**
     * All the REST methods related to answers logic
     * @param type $args
     */
    private function loadAnswerLogic($args) {
        if (array_key_exists('post', $args)) {
            $this->postAnswer();
        } else if (array_key_exists('delete', $args)) {
            $this->deleteAnswer();
        } else if (array_key_exists('update', $args)) {
            $this->updateAnswer();
        } else if (array_key_exists('promote', $args)) {
            $this->promoteAnswer();
        }
    }

    /**
     * All the REST methods related to user profile logic
     * @param type $args
     */
    private function loadProfileLogic($args) {
        if (array_key_exists('details', $args)) {
            $this->getUserDetails($args['details']);
        } else if (array_key_exists('fulldetails', $args)) {
            $this->getFullUserDetails($args['fulldetails']);
        } else if (array_key_exists('post', $args)) {
            $this->updateUserDetails($args['post']);
        } else if (array_key_exists('delete', $args)) {
            $this->deleteUserProfile();
        } else if (array_key_exists('changepassword', $args)) {
            $this->changeUserPassword($args['changepassword']);
        }
    }

    /**
     * All the REST methods related to admin logic
     * @param type $args
     */
    private function loadAdminLogic($args) {
        if (array_key_exists('details', $args)) {
            $this->getDashboardDetails($args['details']);
        } else if (array_key_exists('question', $args)) {
            if ($args['question'] === 'details') {
                $this->getAdminQuestions();
            } else if ($args['question'] === 'flagged') {
                $this->getAdminFlaggedQuestions();
            }
        } else if (array_key_exists('flags', $args)) {
            $this->getAdminFlagged();
        } else if (array_key_exists('answer', $args)) {
            $this->getAdminAnswers();
        } else if (array_key_exists('user', $args)) {
            if ($args['user'] === 'details') {
                $this->getAdminUsers();
            } else if ($args['user'] === 'delete') {
                $this->getAdminDeleteUsers();
            } else if ($args['user'] === 'students') {
                $this->getAdminStudents();
            } else if ($args['user'] === 'promote') {
                $this->getAdminStudentsPromote();
            }
        } else if (array_key_exists('requests', $args)) {
            if ($args['requests'] === 'tutor') {
                $this->getAdminTutorRequests();
            } else if ($args['requests'] === 'delete') {
                $this->getAdminDeleteRequests();
            }
        } else if (array_key_exists('tutor', $args)) {
            if ($args['tutor'] === 'accept') {
                $this->updateAdminTutorRequests(true);
            } else if ($args['tutor'] === 'decline') {
                $this->updateAdminTutorRequests(false);
            }
        } else if (array_key_exists('deletion', $args)) {
            $this->getAdminDeleteUserOnRequest();
        }
    }

    /**
     * All the methods related to index.php/api/auth
     */

    /**
     * Authentication logic to authenticate a user when logging in
     */
    private function authenticate() {
        $username = $this->input->post('uname');
        $password = $this->input->post('pword');
        $rememberLogin = $this->input->post('remember');
        $user = $this->authlib->login($username, $password, $rememberLogin);
        if ($user) {
            $isAdmin = $this->permlib->isAdmin($user['username']);
            $response['isAdmin'] = $isAdmin;
        }

        if ($user != false) {
            $response['message'] = 'correct';
        } else {
            $response['message'] = 'wrong';
        }
        echo json_encode($response);
    }

    /**
     * Create a user logic to create a new user
     */
    private function createaccount() {
        $name = $this->input->post('name');
        $username = $this->input->post('uname');
        $password = $this->input->post('pword_confirmation');
        $conf_password = $this->input->post('pword');
        $email = $this->input->post('email');
        $website = $this->input->post('website');

        $isTutor = $this->input->post('isTutor');
        if ($isTutor === 'true') {
            // Register as a tutor
            $linkedin = $this->input->post('linkedIn');
            $sourl = $this->input->post('sOProfile');
            if (!($errmsg = $this->authlib->registerTutor($name, $username, $password, $conf_password, $email, $website, $linkedin, $sourl))) {
                $req = new Request();
                $time = time();
                $formattedDate = date("Y-m-d H:i:s", $time);
                $req->rDate = $formattedDate;
                $req->rTypeId = 2;
                $req->userId = $this->ci->User->getUserIdByName($username);
                $req->save();
                $response['message'] = 'Success';
                $response['type'] = 'Your request was sent successfully. You will be emailed when accepted by our admins';
            } else {
                $response['message'] = 'Error';
                $response['type'] = $errmsg;
            }
            echo json_encode($response);
        } else {
            // Register as a student
            if (!($errmsg = $this->authlib->register($name, $username, $password, $conf_password, $email, $website))) {
                $response['message'] = 'Success';
                $response['type'] = 'Your account was created successfully! Please log in.';
            } else {
                $response['message'] = 'Error';
                $response['type'] = $errmsg;
            }
            echo json_encode($response);
        }
    }

    /**
     * Forgot password logic to reset a password of a user
     * @return type
     */
    private function forgotPass() {
        $email = $this->input->post('email');
        if (isset($email) && !empty($email)) {
            $this->load->library('form_validation');
            // Checking if this is a valid email or not
            $this->form_validation->set_rules('email', 'Email Address', 'trim|required|min_length[6]|valid_email|xss_clean');

            if ($this->form_validation->run() == FALSE) {
                // Validation failed. Send the error messages back to the forgot password view
                $response['message'] = "Error";
                $response['type'] = "Please enter a valid email address";
                echo json_encode($response);
                return;
            } else {
                $res = $this->authlib->sendResetLink($email);
                if ($res === true) {
                    $response['message'] = "Success";
                    $response['type'] = "A password reset link has been sent to your email";
                    echo json_encode($response);
                    return;
                } else {
                    $response['message'] = "Error";
                    $response['type'] = $res;
                    echo json_encode($response);
                    return;
                }
            }
        } else {
            $response['message'] = "Error";
            $response['type'] = "Please enter a valid email address";
            echo json_encode($response);
            return;
        }
    }

    /**
     * Reset password logic to update the new password of the user
     * @return type
     */
    private function resetPass() {
        $email = $this->input->post('email');
        $hash = $this->input->post('hash');
        $pass = $this->input->post('pass');
        $res = $this->authlib->resetPass($email, $hash, $pass);
        if ($res === true) {
            $response['message'] = "Success";
            $response['type'] = "Your password is updated successfully";
            echo json_encode($response);
            return;
        } else {
            $response['message'] = "Error";
            $response['type'] = $res;
            echo json_encode($response);
            return;
        }
    }

    /**
     * All the methods related to index.php/api/search
     */

    /**
     * Basic search logic with pagination support
     */
    private function searchQuestions() {
        $offset = $this->input->get('page');
        ($offset === NULL) ? 0 : $offset;
        $query = $this->input->get('query');
        if (strlen($query) < 3) {
            $response['message'] = "Error";
            $response['type'] = "You need to enter atleast 3 characters to perform a search";
        } else {
            $results = $this->searchlib->search($query, $offset);
            if (count($results) > 0) {
                $response['message'] = "Success";
                $response['results'] = $results;
                $totCount = $this->ci->searchlib->getSearchPageCount($query, "basic");
                $response['totalCount'] = $totCount['totalCount'];
                $response['totalResCount'] = $totCount['totalResCount'];
            } else {
                $response['message'] = "Error";
                $response['type'] = "Sorry, your query returned no matches!";
            }
        }
        echo json_encode($response);
    }

    /**
     * Advanced search logic with pagination support
     */
    private function advSearchQuestions() {
        $advWords = $this->input->post('Words');
        $advPhrase = $this->input->post('Phrase');
        $advTags = $this->input->post('Tags');
        $advCategory = $this->input->post('Category');
        $offset = $this->input->post('Offset');

        if (strlen($advPhrase) < 3 && ($advWords === '' && $advTags === '' && $advCategory === '0')) {
            $response['message'] = "Error";
            $response['type'] = "Please enter more than 3 character to search";
        } else {
            $results = $this->searchlib->advSearch($advWords, $advPhrase, $advTags, $advCategory, $offset);
            if (count($results) > 0) {
                $response['message'] = "Success";
                $response['results'] = $results;
                $totCount = $this->ci->searchlib->getAdvSearchPageCount($advWords, $advPhrase, $advTags, $advCategory);
                $response['totalCount'] = $totCount['totalCount'];
                $response['totalResCount'] = $totCount['totalResCount'];
            } else {
                $response['message'] = "Error";
                $response['type'] = "Sorry, your query returned no matches!";
            }
        }
        echo json_encode($response);
    }

    /**
     * All the methods related to questions
     */

    /**
     * Question posting logic
     */
    private function postQuestion() {
        $qTitle = $this->input->post('Title');
        $qDesc = $this->input->post('Description');
        $qTags = $this->input->post('Tags');
        $qCategory = $this->input->post('Category');
        $qAskerName = $this->input->post('AskerName');

        if ($this->questionslib->postQuestion($qTitle, $qDesc, $qTags, $qCategory, $qAskerName)) {
            $response["message"] = "Success";
        } else {
            $response["message"] = "Error";
        }

        echo json_encode($response);
    }

    /**
     * Get question details logic
     * @param type $args
     */
    private function getDetails($args) {
        $questionId = $args['details'];
        $questionDetails = $this->questionslib->getQuestionDetails($questionId);

        if ($questionDetails != NULL) {
            $response["message"] = "Success";
            $response['questionDetails'] = $questionDetails;
        } else {
            $response["message"] = "Error";
        }

        echo json_encode($response);
    }

    /**
     * Get recent questions logic
     * @param type $offset
     */
    private function getRecent($offset) {
        ($offset === NULL) ? 0 : $offset;
        $questions = $this->ci->questionslib->getRecentQuestions($offset);
        $response['results'] = $questions;
        $response['totalCount'] = $this->ci->Question->getRecentQuestionsCount();
        echo json_encode($response);
    }

    /**
     * Get popular questions logic
     * @param type $offset
     */
    private function getPopular($offset) {
        ($offset === NULL) ? 0 : $offset;
        $questions = $this->ci->questionslib->getPopularQuestions($offset);
        $response['results'] = $questions;
        $response['totalCount'] = $this->ci->Question->getPopularQuestionsCount();
        echo json_encode($response);
    }

    /**
     * Get unanswered questions logic
     * @param type $offset
     */
    private function getUnanswered($offset) {
        ($offset === NULL) ? 0 : $offset;
        $questions = $this->ci->questionslib->getUnansweredQuestions($offset);
        $response['results'] = $questions;
        $response['totalCount'] = $this->ci->Question->getUnansweredQuestionsCount();
        echo json_encode($response);
    }

    /**
     * Get all un ordered questions list
     * @param type $offset
     */
    private function getAll($offset) {
        ($offset === NULL) ? 0 : $offset;
        $questions = $this->ci->questionslib->getAllQuestions($offset);
        $response['results'] = $questions;
        $response['totalCount'] = $this->ci->Question->getAllQuestionsCounts();
        echo json_encode($response);
    }

    /**
     * Delete questions logic
     */
    private function deleteQuestion() {
        $username = $this->input->post('username');
        $qId = $this->input->post('questionId');

        $name = $this->authlib->is_loggedin();
        if ($name === $username) {
            $status = $this->questionslib->deleteQuestion($username, $qId);
            if ($status) {
                $res = array("message" => "Success", "type" => "Question was deleted successfully!");
                echo json_encode($res);
            } else {
                $res = array("message" => "Error", "type" => "You do not have permissions to delete this question");
                echo json_encode($res);
            }
        } else {
            $res = array("message" => "Error", "type" => "You do not have permissions to delete this question");
            echo json_encode($res);
        }

        if ($name === false) {
            $res = array("message" => "Error", "type" => "You do not have permissions to delete this question");
            echo json_encode($res);
        }
    }

    /**
     * Update a question logic
     */
    private function updateQuestion() {
        $qTitle = $this->input->post('Title');
        $qDesc = $this->input->post('Description');
        $qTags = $this->input->post('Tags');
        $qCategory = $this->input->post('Category');
        $qAskerName = $this->input->post('AskerName');
        $qId = $this->input->post('questionId');

        $name = $this->authlib->is_loggedin();
        if ($name === $qAskerName) {
            $this->questionslib->updateQuestion($qTitle, $qDesc, $qTags, $qCategory, $qAskerName, $qId);
            $res = array("message" => "Success", "type" => "Question was updated successfully!");
            echo json_encode($res);
        }

        if ($name === false) {
            $res = array("message" => "Error", "type" => "You do not have permissions to edit the question");
            echo json_encode($res);
        }
    }

    /**
     * Close a question so that no further changes or answerscan be given
     * @return type
     */
    private function closeQuestion() {
        $qId = $this->input->post('questionId');
        $username = $this->input->post('username');
        $closeReason = $this->input->post('reason');

        if ($closeReason === '') {
            $res = array("message" => "Error", "type" => "Please enter a reason to close this question");
            echo json_encode($res);
            return;
        }

        $name = $this->authlib->is_loggedin();
        if ($name === $username) {
            if ($this->permlib->userHasPermission($username, "ANSWER_QUESTION")) {
                $result = $this->questionslib->closeQuestion($username, $qId, $closeReason);
                if ($result === true) {
                    $res = array("message" => "Success", "type" => "Question was closed successfully!");
                    echo json_encode($res);
                } else {
                    $res = array("message" => "Success", "type" => "Question was closed successfully!");
                    echo json_encode($res);
                }
            } else {
                $res = array("message" => "Error", "type" => "You do not have permissions to close the question");
                echo json_encode($res);
            }
        }

        if ($name === false || $name !== $username) {
            $res = array("message" => "Error", "type" => "You do not have permissions to close the question");
            echo json_encode($res);
        }
    }

    /**
     * Flag question logic
     */
    private function flagQuestion() {
        $qId = $this->input->post('questionId');
        $username = $this->input->post('username');
        $name = $this->authlib->is_loggedin();

        if ($name === $username) {
            if ($this->userlib->getUserPoints($username) > 5) {
                $result = $this->questionslib->flagQuestion($username, $qId);
                if ($result === true) {
                    $res = array("message" => "Success", "type" => "Question was flagged successfully!");
                    echo json_encode($res);
                } else {
                    $res = array("message" => "Error", "type" => "You have already flagged this question!");
                    echo json_encode($res);
                }
            } else {
                $res = array("message" => "Error", "type" => "You do not have enough points to flag this question");
                echo json_encode($res);
            }
        }

        if ($name === false || $name !== $username) {
            $res = array("message" => "Error", "type" => "You do not have permissions to flag the question");
            echo json_encode($res);
        }
    }

    /**
     * All methods related to tags
     */

    /**
     * Get all the tags. Used for tag search and question asking screen.
     */
    private function getAllTags() {
        $allTags = $this->Tag->get();
        echo json_encode($allTags);
    }

    /**
     * Get Tags for Recent questions
     * @param type $offset
     * @param type $tagname
     */
    private function getRecentTags($offset, $tagname) {
        ($offset === NULL) ? 0 : $offset;
        $questions = $this->ci->questionslib->getRecentQuestionsWithTag($offset, $tagname);
        $response['results'] = $questions;
        $response['totalCount'] = $this->ci->Question->getRecentQuestionsWithTagCount($tagname);
        echo json_encode($response);
    }

    /**
     * Get tags for popular questions
     * @param type $offset
     * @param type $tagname
     */
    private function getPopularTags($offset, $tagname) {
        ($offset === NULL) ? 0 : $offset;
        $questions = $this->ci->questionslib->getPopularQuestionsWithTag($offset, $tagname);
        $response['results'] = $questions;
        $response['totalCount'] = $this->ci->Question->getPopularQuestionsWithTagCount($tagname);
        echo json_encode($response);
    }

    /**
     * Get tags for unanswered questions
     * @param type $offset
     * @param type $tagname
     */
    private function getUnansweredTags($offset, $tagname) {
        ($offset === NULL) ? 0 : $offset;
        $questions = $this->ci->questionslib->getUnansweredQuestionsWithTag($offset, $tagname);
        $response['results'] = $questions;
        $response['totalCount'] = $this->ci->Question->getUnansweredQuestionsWithTagCount($tagname);
        echo json_encode($response);
    }

    /**
     * Get tags for all questions
     * @param type $offset
     * @param type $tagname
     */
    private function getAllTagsForTag($offset, $tagname) {
        ($offset === NULL) ? 0 : $offset;
        $questions = $this->ci->questionslib->getAllQuestionsWithTag($offset, $tagname);
        $response['results'] = $questions;
        $response['totalCount'] = $this->ci->Question->getAllQuestionsWithTagCount($tagname);
        echo json_encode($response);
    }

    /**
     * All methods related to categories
     */

    /**
     * Get Categories for recent tab
     * @param type $offset
     * @param type $catname
     */
    private function getRecentCat($offset, $catname) {
        ($offset === NULL) ? 0 : $offset;
        $questions = $this->ci->questionslib->getRecentQuestionsWithCat($offset, $catname);
        $response['results'] = $questions;
        $response['totalCount'] = $this->ci->Question->getRecentQuestionsWithCatCount($catname);
        echo json_encode($response);
    }

    /**
     * Get Categories for popular tab
     * @param type $offset
     * @param type $catname
     */
    private function getPopularCat($offset, $tagname) {
        ($offset === NULL) ? 0 : $offset;
        $questions = $this->ci->questionslib->getPopularQuestionsWithCat($offset, $tagname);
        $response['results'] = $questions;
        $response['totalCount'] = $this->ci->Question->getPopularQuestionsWithCatCount($tagname);
        echo json_encode($response);
    }

    /**
     * Get Categories for unanswered tab
     * @param type $offset
     * @param type $catname
     */
    private function getUnansweredCat($offset, $tagname) {
        ($offset === NULL) ? 0 : $offset;
        $questions = $this->ci->questionslib->getUnansweredQuestionsWithCat($offset, $tagname);
        $response['results'] = $questions;
        $response['totalCount'] = $this->ci->Question->getUnansweredQuestionsWithCatCount($tagname);
        echo json_encode($response);
    }

    /**
     * All methods related to voting
     */

    /**
     * Vote up logic
     * @param type $arg
     * @return type
     */
    private function voteUp($arg) {
        if (strtolower($arg) === "question") {
            $qId = $this->input->post('questionId');
            $username = $this->input->post('username');

            if (!($this->authlib->is_loggedin() === $username)) {
                $response['message'] = 'Error';
                $response['type'] = 'You need to login before voting!';
                echo json_encode($response);
                return;
            } else if ($username === $this->User->getUserById($this->Question->getAskerUserId($qId))) {
                $response['message'] = 'Error';
                $response['type'] = 'You cannot vote on your own question!';
                echo json_encode($response);
                return;
            } else {
                $votes = $this->voteslib->voteUp(TRUE, $qId, $username);
                if ($votes == TRUE) {
                    $response['message'] = 'Success';
                    $response['votes'] = $this->ci->Question->getNetVotes($qId);
                    echo json_encode($response);
                } else {
                    $response['message'] = 'Error';
                    $response['type'] = 'You have already voted on this question!';
                    echo json_encode($response);
                    return;
                }
            }
        } else if (strtolower($arg) === "answer") {
            $ansId = $this->input->post('answerId');
            $username = $this->input->post('username');

            if (!($this->authlib->is_loggedin() === $username)) {
                $response['message'] = 'Error';
                $response['type'] = 'You need to login before voting!';
                echo json_encode($response);
                return;
            } else if ($username === $this->User->getUserById($this->Answer->getAnsweredUserId($ansId))) {
                $response['message'] = 'Error';
                $response['type'] = 'You cannot vote on your own answer!';
                echo json_encode($response);
                return;
            } else {
                $votes = $this->voteslib->voteUp(FALSE, $ansId, $username);
                if ($votes == TRUE) {
                    $response['message'] = 'Success';
                    $response['votes'] = $this->ci->Answer->getNetVotes($ansId);
                    echo json_encode($response);
                } else {
                    $response['message'] = 'Error';
                    $response['type'] = 'You have already voted on this question!';
                    echo json_encode($response);
                    return;
                }
            }
        } else {
            $response['message'] = 'Error';
            $response['type'] = 'Malformed URL!';
            echo json_encode($response);
        }
    }

    /**
     * Vote down logic
     * @param type $arg
     * @return type
     */
    private function voteDown($arg) {
        if (strtolower($arg) === "question") {
            $qId = $this->input->post('questionId');
            $username = $this->input->post('username');

            if (!($this->authlib->is_loggedin() === $username)) {
                $response['message'] = 'Error';
                $response['type'] = 'You need to login before voting!';
                echo json_encode($response);
                return;
            } else if ($username === $this->User->getUserById($this->Question->getAskerUserId($qId))) {
                $response['message'] = 'Error';
                $response['type'] = 'You cannot vote on your own question!';
                echo json_encode($response);
                return;
            } else {
                $votes = $this->voteslib->voteDown(TRUE, $qId, $username);
                if ($votes != FALSE) {
                    $response['message'] = 'Success';
                    $response['votes'] = $this->ci->Question->getNetVotes($qId);
                    echo json_encode($response);
                } else {
                    $response['message'] = 'Error';
                    $response['type'] = 'You have already voted on this question!';
                    echo json_encode($response);
                    return;
                }
            }
        } else if (strtolower($arg) === "answer") {
            $ansId = $this->input->post('answerId');
            $username = $this->input->post('username');

            if (!($this->authlib->is_loggedin() === $username)) {
                $response['message'] = 'Error';
                $response['type'] = 'You need to login before voting!';
                echo json_encode($response);
                return;
            } else if ($username === $this->User->getUserById($this->Answer->getAnsweredUserId($ansId))) {
                $response['message'] = 'Error';
                $response['type'] = 'You cannot vote on your own answer!';
                echo json_encode($response);
                return;
            } else {
                $votes = $this->voteslib->voteDown(FALSE, $ansId, $username);
                if ($votes == TRUE) {
                    $response['message'] = 'Success';
                    $response['votes'] = $this->ci->Answer->getNetVotes($ansId);
                    echo json_encode($response);
                } else {
                    $response['message'] = 'Error';
                    $response['type'] = 'You have already voted on this question!';
                    echo json_encode($response);
                    return;
                }
            }
        } else {
            $response['message'] = 'Error';
            $response['type'] = 'Malformed URL!';
            echo json_encode($response);
        }
    }

    /**
     * All methods related to Answers
     */

    /**
     * Post Answer Logic
     */
    private function postAnswer() {
        $quesId = $this->input->post('questionId');
        $tutorName = $this->input->post('username');
        $description = $this->input->post('description');

        if ($tutorName) {
            if ($this->permlib->userHasPermission($tutorName, "ANSWER_QUESTION")) {
                if ($this->questionslib->isQuestionClosed($quesId)) {
                    $response["message"] = "Error";
                    $response["type"] = "This question is closed. Therefore you cannot post an answer.";
                } else {
                    if ($this->questionslib->postAnswer($quesId, $tutorName, $description)) {
                        $response["message"] = "Success";
                    } else {
                        $response["message"] = "Error";
                        $response["type"] = "Oops, something went wrong!";
                    }
                }
            } else {
                $response["message"] = "Error";
                $response["type"] = "Sorry, you need to have permissions to post an answer. You may want to request for a tutor account.";
            }
        } else {
            $response["message"] = "Error";
            $response["type"] = "You need to log in before posting an answer";
        }

        echo json_encode($response);
    }

    /**
     * Delete Answer Logic
     */
    private function deleteAnswer() {
        $username = $this->input->post('username');
        $ansId = $this->input->post('answerId');

        $name = $this->authlib->is_loggedin();
        if ($name === $username) {
            $status = $this->questionslib->deleteAnswer($username, $ansId);
            if ($status) {
                $res = array("message" => "Success", "type" => "Answer was deleted successfully!");
                echo json_encode($res);
                return;
            } else {
                $res = array("message" => "Error", "type" => "You do not have permissions to delete this answer");
                echo json_encode($res);
                return;
            }
        } else {
            $res = array("message" => "Error", "type" => "You do not have permissions to delete this answer");
            echo json_encode($res);
            return;
        }

        if ($name === false) {
            $res = array("message" => "Error", "type" => "You do not have permissions to delete this answer");
            echo json_encode($res);
            return;
        }
    }

    /**
     * Update Answer Logic
     */
    private function updateAnswer() {
        $quesId = $this->input->post('questionId');
        $tutorName = $this->input->post('username');
        $description = $this->input->post('description');
        $ansId = $this->input->post('answerId');

        $name = $this->authlib->is_loggedin();
        if ($name) {
            if ($this->permlib->userHasPermission($tutorName, "ANSWER_QUESTION")) {
                if ($this->questionslib->updateAnswer($quesId, $tutorName, $description, $ansId)) {
                    $response["message"] = "Success";
                } else {
                    $response["message"] = "Error";
                    $response["type"] = "Oops, something went wrong!";
                }
            } else {
                $response["message"] = "Error";
                $response["type"] = "Sorry, you need to have permissions to post an answer. You may want to request for a tutor account.";
            }
        } else {
            $response["message"] = "Error";
            $response["type"] = "You need to log in before posting an answer";
        }
        echo json_encode($response);
    }

    /**
     * Promote Answer Logic
     */
    private function promoteAnswer() {
        $quesId = $this->input->post('questionId');
        $promotersName = $this->input->post('username');
        $ansId = $this->input->post('answerId');

        $name = $this->authlib->is_loggedin();
        if ($name) {
            if ($promotersName === $name) {
                if ($this->questionslib->promoteAnswer($quesId, $ansId)) {
                    $response["message"] = "Success";
                    $response["type"] = "Answer chosen as the best answer for this question";
                } else {
                    $response["message"] = "Error";
                    $response["type"] = "Oops, something went wrong!";
                }
            } else {
                $response["message"] = "Error";
                $response["type"] = "Sorry, you need to have permissions to promote an answer. You must be the author of this question";
            }
        } else {
            $response["message"] = "Error";
            $response["type"] = "You need to log in before promoting an answer";
        }
        echo json_encode($response);
    }

    /**
     * All methods related to user profiles
     */

    /**
     * Get a user's details via username
     * @param type $username
     */
    private function getUserDetails($username) {
        $res = $this->userlib->getUserDetails($username);

        if ($res === false) {
            $res = array("message" => "Error", "type" => "User not found");
        }
        echo json_encode($res);
    }

    /**
     * Get a user's full details via username
     * @param type $username
     */
    private function getFullUserDetails($username) {
        $name = $this->authlib->is_loggedin();
        if ($name === $username) {
            $res = $this->userlib->getFullUserDetails($username);
            echo json_encode($res);
        }

        if ($name === false || $name != $username) {
            $res = array("message" => "Error", "type" => "You do not have permissions");
            echo json_encode($res);
        }
    }

    /**
     * Update a user's details via username
     * @param type $username
     */
    private function updateUserDetails($username) {
        $name = $this->authlib->is_loggedin();
        if ($name === $username) {
            $in = $this->input->post(NULL, true);
            $res = $this->userlib->updateUserDetails($username, $in);
            echo json_encode($res);
        }

        if ($name === false || $name !== $username) {
            $res = array("message" => "Error", "type" => "You do not have permissions");
            echo json_encode($res);
        }
    }

    /**
     * Delete a user profile
     * @return type
     */
    private function deleteUserProfile() {
        $name = $this->authlib->is_loggedin();
        $username = $this->input->post('username');
        $hash = $this->input->post('pword');
        if ($name === $username) {
            if ($this->userlib->deactiveUserProfile($username, $hash)) {
                $this->authlib->logout();
                $res = array("message" => "Success", "type" => "User deactivated successfully");
                echo json_encode($res);
                return;
            } else {
                $res = array("message" => "Error", "type" => "Something went wrong");
                echo json_encode($res);
                return;
            }
        } else {
            $res = array("message" => "Error", "type" => "You do not have permissions");
            echo json_encode($res);
            return;
        }

        if ($name === false) {
            $res = array("message" => "Error", "type" => "You do not have permissions");
            echo json_encode($res);
            return;
        }
    }

    /**
     * Change a user's password via username
     * @param type $username
     */
    private function changeUserPassword($username) {
        $name = $this->authlib->is_loggedin();
        if ($name === $username) {

            $username = $this->input->post('username');
            $oldPw = $this->input->post('oldPw');
            $newPw = $this->input->post('newPw');
            $res = $this->userlib->updatePassword($username, $oldPw, $newPw);
            if ($res === true) {
                $res = array("message" => "Success", "type" => "Password changed successfully");
                echo json_encode($res);
            } else {
                $res = array("message" => "Error", "type" => $res);
                echo json_encode($res);
            }
        }

        if ($name === false || $name !== $username) {
            $res = array("message" => "Error", "type" => "You do not have permissions");
            echo json_encode($res);
        }
    }

    /**
     * All methods related to admin dashboard
     */

    /**
     * Get Admin dashboard details
     * @param type $option
     */
    private function getDashboardDetails($option) {
        if ($option === 'basic') {
            $name = $this->authlib->is_loggedin();
            $username = $this->input->post('username');
            if ($username === $name && $username === 'admin') {
                $reponse['message'] = "Success";
                $reponse['data'] = $this->adminlib->getBasicStats();
                echo json_encode($reponse);
            } else {
                $reponse['message'] = "Error";
                $reponse['type'] = "You are not authorized to view this content";
                echo json_encode($reponse);
            }
        }
    }

    /*
     * Get admin panel questions list
     */

    private function getAdminQuestions() {
        $name = $this->authlib->is_loggedin();
        //$username = $this->input->post('username');
        if ($name) {
            $reponse['message'] = "Success";
            $reponse['aaData'] = $this->adminlib->getQuestions();
            echo json_encode($reponse);
        } else {
            $reponse['message'] = "Error";
            $reponse['type'] = "You are not authorized to view this content";
            echo json_encode($reponse);
        }
    }

    /*
     * Get admin panel flagged questions list
     */

    private function getAdminFlaggedQuestions() {
        $name = $this->authlib->is_loggedin();
        //$username = $this->input->post('username');
        if ($name) {
            $reponse['message'] = "Success";
            $reponse['aaData'] = $this->adminlib->getFlaggedQuestions();
            echo json_encode($reponse);
        } else {
            $reponse['message'] = "Error";
            $reponse['type'] = "You are not authorized to view this content";
            echo json_encode($reponse);
        }
    }

    /*
     * Get admin panel answers list
     */

    private function getAdminAnswers() {
        $name = $this->authlib->is_loggedin();
        //$username = $this->input->post('username');
        if ($name) {
            $reponse['message'] = "Success";
            $reponse['aaData'] = $this->adminlib->getAnswers();
            echo json_encode($reponse);
        } else {
            $reponse['message'] = "Error";
            $reponse['type'] = "You are not authorized to view this content";
            echo json_encode($reponse);
        }
    }

    /*
     * Get admin panel users list
     */

    private function getAdminUsers() {
        $name = $this->authlib->is_loggedin();
        //$username = $this->input->post('username');
        if ($name) {
            $reponse['message'] = "Success";
            $reponse['aaData'] = $this->adminlib->getUsers();
            echo json_encode($reponse);
        } else {
            $reponse['message'] = "Error";
            $reponse['type'] = "You are not authorized to view this content";
            echo json_encode($reponse);
        }
    }

    /*
     * Get admin panel delete user list
     */

    private function getAdminDeleteUsers() {
        $name = $this->authlib->is_loggedin();
        //$username = $this->input->post('username');
        $userId = $this->input->post('userId');
        if ($name && $userId != null) {
            if ($this->adminlib->deleteUser($userId)) {
                $reponse['message'] = "Success";
                $reponse['type'] = "User deleted successfully";
                echo json_encode($reponse);
            } else {
                $reponse['message'] = "Error";
                $reponse['type'] = "Something went wrong";
                echo json_encode($reponse);
            }
        } else {
            $reponse['message'] = "Error";
            $reponse['type'] = "You are not authorized to view this content";
            echo json_encode($reponse);
        }
    }

    /*
     * Get admin panel tutors list
     */

    private function getAdminTutorRequests() {
        $name = $this->authlib->is_loggedin();
        //$username = $this->input->post('username');
        if ($name) {
            $reponse['message'] = "Success";
            $reponse['aaData'] = $this->adminlib->getAdminTutorRequests();
            echo json_encode($reponse);
        } else {
            $reponse['message'] = "Error";
            $reponse['type'] = "You are not authorized to view this content";
            echo json_encode($reponse);
        }
    }

    /*
     * Get admin panel delete profile requests list
     */

    private function getAdminDeleteRequests() {
        $name = $this->authlib->is_loggedin();
        //$username = $this->input->post('username');
        if ($name) {
            $reponse['message'] = "Success";
            $reponse['aaData'] = $this->adminlib->getAdminDeleteRequests();
            echo json_encode($reponse);
        } else {
            $reponse['message'] = "Error";
            $reponse['type'] = "You are not authorized to view this content";
            echo json_encode($reponse);
        }
    }

    /**
     * Update tutors requests in the admin panel
     * @param type $isAccept
     */
    private function updateAdminTutorRequests($isAccept) {
        $name = $this->authlib->is_loggedin();
        $userId = $this->input->post('tutorId');
        $rId = $this->input->post('rId');

        if ($isAccept) {
            // Accept logic
            if ($name) {
                $reponse['message'] = "Success";
                $this->adminlib->updateRequest(true, $userId, $rId);
                $reponse['type'] = "Tutor profile activated!";
                echo json_encode($reponse);
            } else {
                $reponse['message'] = "Error";
                $reponse['type'] = "You are not authorized to view this content";
                echo json_encode($reponse);
            }
        } else {
            // Decline logic
            if ($name) {
                $reponse['message'] = "Success";
                $this->adminlib->updateRequest(false, $userId, $rId);
                $reponse['type'] = "Tutor profile deleted!";
                echo json_encode($reponse);
            } else {
                $reponse['message'] = "Error";
                $reponse['type'] = "You are not authorized to view this content";
                echo json_encode($reponse);
            }
        }
    }

    /**
     * Get user profiles waiting to get deleted
     */
    private function getAdminDeleteUserOnRequest() {
        $rId = $this->input->post('rId');
        $req = new Request();
        $req->load($rId);
        $req->delete();
        $this->getAdminDeleteUsers();
    }

    /**
     * Get students list to admin panel
     */
    private function getAdminStudents() {
        $name = $this->authlib->is_loggedin();

        if ($name) {
            $reponse['message'] = "Success";
            $reponse['aaData'] = $this->adminlib->getAdminStudents();
            echo json_encode($reponse);
        } else {
            $reponse['message'] = "Error";
            $reponse['type'] = "You are not authorized to view this content";
            echo json_encode($reponse);
        }
    }

    /*
     * Get admin panel students to promote
     */

    private function getAdminStudentsPromote() {
        $name = $this->authlib->is_loggedin();
        //$username = $this->input->post('username');
        $userId = $this->input->post('userId');
        if ($name && $userId != null) {
            if ($this->adminlib->promoteUser($userId)) {
                $reponse['message'] = "Success";
                $reponse['type'] = "Student promoted successfully";
                echo json_encode($reponse);
            } else {
                $reponse['message'] = "Error";
                $reponse['type'] = "Something went wrong";
                echo json_encode($reponse);
            }
        } else {
            $reponse['message'] = "Error";
            $reponse['type'] = "You are not authorized to view this content";
            echo json_encode($reponse);
        }
    }

}

?>
