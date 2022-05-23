<?php
namespace Modules\site\Controllers;
class Site {
    
    public function index($params) {
        \Utils\Layout::view("home", $params);
    }
    public function home($params) {
        \Utils\Layout::view("home", $params);
    }
    public function aboutus($params) {
        \Utils\Layout::view("aboutus", $params);
    }
    public function contacts($params) {
        \Utils\Layout::view("contact", $params);
    }
    public function services($params) {
        \Utils\Layout::view("services", $params);
    }
    public function faqs($params) {
        \Utils\Layout::view("faqs", $params);
    }
    public function terms($params) {
        \Utils\Layout::view("terms", $params);
    }
    public function docsapi($params) {
        \Utils\Layout::view("docsapi", $params);
    }
    public function privacy($params) {
        \Utils\Layout::view("privacy", $params);
    }
    public function market($params) {
        \Utils\Layout::view("market", $params);
    }
    public function compliance($params) {
        \Utils\Layout::view("compliance", $params);
    }
    public function in1888($params) {
        \Utils\Layout::view("in1888", $params);
    }
}
