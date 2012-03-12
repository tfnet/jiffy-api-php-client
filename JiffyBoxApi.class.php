<?php

/**
 *
 * @author siebl
 *
 */
class JiffyBox {

    private $JB_API_HOST = '';
    private $DEFAULT_PLAN_ID = '';

    /**
     *
     * @param string $token
     * @param integer $id
     */
    public function __construct($token, $id = NULL) {

        $this->JB_API_HOST = 'https://api.jiffybox.de/' . $token . '/v1.0/';
        $this->DEFAULT_PLAN_ID = 10;
        $this->id = $id;
        if ($id) {
            $this->getInfo ();

        }

    }

    /**
     *
     * @param integer $id
     * @param string $method
     * @param array $data
     * @param string $command
     */
    private function requestCurl($id, $method = 'GET', $data = array(), $command = 'jiffyBoxes') {

        if ($id != - 1) {
            $ch = curl_init ( $this->JB_API_HOST . $command . '/' . $id );
        } else {
            $ch = curl_init ( $this->JB_API_HOST . $command );
        }

        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );

        if (strtoupper ( $method ) == 'POST') {
            curl_setopt ( $ch, CURLOPT_POST, 1 );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
        }
        if (strtoupper ( $method ) == 'GET')
            curl_setopt ( $ch, CURLOPT_HTTPGET, 1 );
        if (strtoupper ( $method ) == 'PUT') {
            curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'PUT' );
            curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
                    'Content-Length: ' . strlen ( $data )
            ) );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
        }

        if (! $data = curl_exec ( $ch )) {
            echo "Curl execusion error.", curl_error ( $ch ) . "\n";

            return FALSE;
        }
        if (isset ( $data->messages [0]->typ ) && $data->messages [0]->type == 'error') {
            die ( $data->messages [0]->message );
        }

        curl_close ( $ch );

        return $data;
    }

    /**
     *
     * @return boolean
     */
    public function getInfo() {

        $json = $this->requestCurl ( $id = $this->id, $method = 'GET' );
        $json = json_decode ( $json );
        if (is_object ( $json ) && ! empty ( $json->result )) {
            foreach ( $json->result as $var => $value ) {
                $this->$var = $value;
            }

            return $json->result;
        } else {

            return FALSE;
        }
    }

    /**
     *
     * @return mixed
     */
    public function getBackups() {

        $json = $this->requestCurl ( $id = $this->id, $method = 'GET', $data = NULL, $command = 'backups' );

        return json_decode ( $json );
    }

    /**
     *
     * @param string $name
     * @param integer $planid
     * @param integer $backupid
     * @param string $distribution
     * @param string $password
     * @param bool $use_sshkey
     * @param string $metadata
     * @return Ambigous <boolean, multitype:, mixed>
     */
    public function create($name, $planid = 10, $backupid = NULL, $distribution = NULL, $password = NULL, $use_sshkey = TRUE, $metadata = NULL) {

        $data = array (

                'name' => $name,
                'planid' => $planid,
                'backupid' => $backupid,
                'distribution' => $distribution,
                'password' => $password,
                'use_sshkey' => $use_sshke,
                'metadata' => $metadata
        );

        return $this->requestCurl ( $id = $this->id, $method = 'POST', $data );
    }

    /**
     *
     * @return Ambigous <boolean, multitype:, mixed>|boolean
     */
    public function stop() {

        if ($this->getStatus () == 'READY') {
            $json = json_encode ( array (
                    'status' => 'SHUTDOWN'
            ) );

            return $this->requestCurl ( $id = $this->id, $method = 'PUT', $data = $json );
        } else {

            return FALSE;
        }
    }

    /**
     *
     * @param integer $planid
     * @return Ambigous <boolean, multitype:, mixed>|boolean
     */
    public function thaw($planid = 10) {

        if ($this->getStatus () == 'FROZEN') {
            $json = json_encode ( array (
                    'status' => 'THAW',
                    'planid' => $planid
            ) );

            return $this->requestCurl ( $id = $this->id, $method = 'PUT', $data = $json );
        } else {

            return FALSE;
        }
    }

    /**
     *  @return bool
     */
    public function freeze() {

        if ($this->getStatus () == 'READY') {
            $json = json_encode ( array (
                    'status' => 'FREEZE'
            ) );

            return $this->requestCurl ( $id = $this->id, $method = 'PUT', $data = $json );
        } else {

            return FALSE;
        }
    }

    /**
     *  @return bool
     */
    public function start() {

        if ($this->getStatus () == 'READY') {
            $json = json_encode ( array (
                    'status' => 'START'
            ) );

            return $this->requestCurl ( $id = $this->id, $method = 'PUT', $data = $json );
        } else {

            return FALSE;
        }
    }

    /**
     *  @return bool
     */
    public function getStatus() {

        $json = $this->requestCurl ( $id = $this->id, $method = 'GET', $data = NULL );
        $json = json_decode ( $json );

        return $json->result->status;
    }

    /**
     *  @return bool
     */
    public function getPlans() {

        $json = $this->requestCurl ( $id = - 1, $method = 'GET', $data = NULL, $command = 'plans' );
        $json = json_decode ( $json );

        return $json->result;
    }

}