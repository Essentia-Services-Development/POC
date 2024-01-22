<?php

class PeepSo3_REST_V1_Endpoint_Test extends PeepSo3_REST_V1_Endpoint {

    public function read() {
        return array('success' => TRUE);
    }

    public function create() {
        return array('success' => TRUE);
    }

    public function edit() {
        return array('success' => TRUE);
    }

    public function delete() {
        return array('success' => TRUE);
    }

    public function can($method) {
        return TRUE;
    }
}