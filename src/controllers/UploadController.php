<?php

class UploadController
{
    public function indexAction()
    {
        $pageTitle = 'Upload page';
        $viewPath = ROOT . '/views/upload/index.php';
        include_once $viewPath;
    }

    public function dataAction()
    {
        $error = null;

        if (is_uploaded_file($_FILES['data']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['data']['name'])['extension']);

            if ($ext == 'dat' || $ext == 'txt') {
                $_SESSION['data'] = explode("\r\n", file_get_contents($_FILES['data']['tmp_name']));
                Redirect::path('/');
            } else {
                $error['message'] = 'The file has an invalid extension';
            }
        } else {
            $error['message'] = 'The file was not uploaded';
        }

        $pageTitle = 'Upload page';
        $viewPath = ROOT . '/views/upload/index.php';
        include_once $viewPath;
    }
}