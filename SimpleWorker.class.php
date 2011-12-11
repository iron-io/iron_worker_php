<?php

class Http_Exception extends Exception{
    const NOT_MODIFIED = 304;
    const BAD_REQUEST = 400;
    const NOT_FOUND = 404;
    const NOT_ALOWED = 405;
    const CONFLICT = 409;
    const PRECONDITION_FAILED = 412;
    const INTERNAL_ERROR = 500;
}
class SimpleWorker_Exception extends Exception{

}

class SimpleWorker{

    //Header Constants
    const header_user_agent = "SimpleWorker PHP v0.1";
    const header_accept = "application/json";
    const header_accept_encoding = "gzip, deflate";
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACEPTED = 202;

    const POST   = 'POST';
    const GET    = 'GET';
    const DELETE = 'DELETE';

    public  $debug_enabled = false;

    private $required_config_fields = array('token','protocol','host','port','api_version');

    private $url;
    private $token;
    private $api_version;
    private $version;
    private $project_id;

    /**
     * @param string|array $config_file_or_options
     *        Array of options or name of config file.
     * Fields in options array or in config:
     * Required:
     * - token
     * - protocol
     * - host
     * - port
     * - api_version
     * Optional:
     * - default_project_id
     */
    function __construct($config_file_or_options){
        $config = $this->getConfigData($config_file_or_options);
        $token              = $config['token'];
        $protocol           = $config['protocol'];
        $host               = $config['host'];
        $port               = $config['port'];
        $api_version        = $config['api_version'];
        $default_project_id = empty($config['default_project_id'])?'':$config['default_project_id'];

        $this->url          = "$protocol://$host:$port/$api_version/";
        $this->token        = $token;
        $this->api_version  = $api_version;
        $this->version      = $api_version;
        $this->project_id   = $default_project_id;
    }

    /**
     * Creates a zip archieve from array of file names
     * @static
     * @param string $base_dir Full path to directory which contain files
     * @param array $files File names, path (both passesed and stored) is relative to $base_dir.
     *        Examples: 'worker.php','lib/file.php'
     * @param string $destination Zip file name.
     * @param bool $overwrite Overwite existing file or not.
     * @return bool
     */
    public static function createZip($base_dir, $files = array(), $destination, $overwrite = false) {
        //if the zip file already exists and overwrite is false, return false
        if(file_exists($destination) && !$overwrite) { return false; }
        if (!empty($base_dir)) $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        //vars
        $valid_files = array();
        //if files were passed in...
        if(is_array($files)) {
            //cycle through each file
            foreach($files as $file) {
                //make sure the file exists
                if(file_exists($base_dir.$file)) {
                    $valid_files[] = $file;
                }
            }
        }
        if(count($valid_files)) {
            $zip = new ZipArchive();
            if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
                return false;
            }
            foreach($valid_files as $file) {
                $zip->addFile($base_dir.$file, $file);
            }
            $zip->close();
            return file_exists($destination);
        }else{
            return false;
        }
    }

    /**
     * Creates a zip archieve with all files and folders inside specific directory.
     * @static
     * @param string $directory
     * @param string $destination
     * @param bool $overwrite
     * @return bool
     */
    public static function zipDirectory($directory, $destination, $overwrite = false){
        if (!file_exists($directory) || !is_dir($directory)) return false;
        $directory = rtrim($directory, DIRECTORY_SEPARATOR);

        $files = self::fileNamesRecursive($directory);

        if (empty($files)) return false;

        return self::createZip($directory, $files, $destination, $overwrite);
    }

    public static function dateRfc3339($timestamp = 0) {

        if (!$timestamp) {
            $timestamp = time();
        }
        $date = date('Y-m-d\TH:i:s', $timestamp);

        $matches = array();
        if (preg_match('/^([\-+])(\d{2})(\d{2})$/', date('O', $timestamp), $matches)) {
            $date .= $matches[1].$matches[2].':'.$matches[3];
        } else {
            $date .= 'Z';
        }
        return $date;
    }

    public function setProjectId($project_id) {
        if (!empty($project_id)){
          $this->project_id = $project_id;
        }
        if (empty($this->project_id)){
            throw new InvalidArgumentException("Please set project_id");
        }
    }

    public function getProjects(){
        $this->setJsonHeaders();
        $projects = json_decode($this->apiCall(self::GET, 'projects'));
        return $projects->projects;
    }

    public function getTasks($project_id = ''){
        $this->setProjectId($project_id);
        $url = "projects/{$this->project_id}/tasks";
        $this->setJsonHeaders();
        $task = json_decode($this->apiCall(self::GET, $url));
        return $task->tasks;
    }

    public function getProjectDetails($project_id = ''){
        $this->setProjectId($project_id);
        $this->setJsonHeaders();
        $url =  "projects/{$this->project_id}";
        return json_decode($this->apiCall(self::GET, $url));
    }

    public function getCodes($project_id = ''){
        $this->setProjectId($project_id);
        $this->setJsonHeaders();
        $url = "projects/{$this->project_id}/codes";
        $codes = json_decode($this->apiCall(self::GET, $url));
        return $codes->codes;
    }

    public function getCodeDetails($code_id, $project_id = ''){
        if (empty($code_id)){
            throw new InvalidArgumentException("Please set code_id");
        }
        $this->setProjectId($project_id);   
        $this->setJsonHeaders();
        $url = "projects/{$this->project_id}/codes/$code_id";
        return json_decode($this->apiCall(self::GET, $url));
    }

    /**
     * Uploads your code package
     * @param string $project_id
     * @param string $filename this file will be launched as worker
     * @param string $zipFilename zip file containing code to execute
     * @param string $name referenceable (unique) name for your worker
     * @return mixed
     */
    public function postCode($project_id, $filename, $zipFilename, $name){
        $this->setProjectId($project_id);
        $this->setPostHeaders();
        $this->headers['Content-Length'] = filesize($zipFilename);
        $ts = time();
        $runtime_type = $this->runtimeFileType($filename);
        $sendingData = array(
            "code_name" => $name,
            "name" => $name,
            "standalone" => True,
            "runtime" => $runtime_type,
            "file_name" => $filename,
            "version" => $this->version,
            "timestamp" => $ts,
            "oauth" => $this->token,
            "class_name" => $name,
            "options" => array(),
            "access_key" => $name);

        $sendingData = json_encode($sendingData);

        // For reference to multi-part encoding in php, see:
        //    http://vedovini.net/2009/08/posting-multipart-form-data-using-php/
        $eol = "\r\n";
        $data = '';
        $mime_boundary = md5(time());
        $data .= '--' . $mime_boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="data"' . $eol . $eol;
        $data .= $sendingData . $eol;
        $data .= '--' . $mime_boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="file"; filename=$zipFilename' . $eol;
        $data .= 'Content-Type: text/plain' . $eol;
        $data .= 'Content-Transfer-Encoding: binary' . $eol . $eol;
        $data .= $this->getFileContent($zipFilename) . $eol;
        $data .= "--" . $mime_boundary . "--" . $eol . $eol; // finish with two eol's!!
 
        $params = array('http' => array(
                  'method' => 'POST',
                  'header' => 'Content-Type: multipart/form-data; boundary=' . $mime_boundary . $eol,
                  'content' => $data
               ));
        $ctx = stream_context_create($params);
        $destination = "{$this->url}projects/{$this->project_id}/codes?oauth={$this->token}";
        $this->debug('destination', $destination);

        $response = file_get_contents($destination, false, $ctx);
        return json_decode($response);
    }


    public function postProject($name){
        $request = array(
            'name' => $name
        );

        $this->setCommonHeaders();
        $res = $this->apiCall(self::POST, 'projects', $request);
        $responce = json_decode($res);
        return $responce->id;
    }

    public function deleteProject($project_id){
        $this->setProjectId($project_id);
        $url = "projects/{$this->project_id}";
        return $this->apiCall(self::DELETE, $url);
    }

    public function deleteCode($project_id, $code_id){
        $this->setProjectId($project_id);
        $url = "projects/{$this->project_id}/codes/$code_id";
        return $this->apiCall(self::DELETE, $url);
    }

    public function deleteTask($project_id, $task_id){
        $this->setProjectId($project_id);
        $this->setCommonHeaders();
        $this->headers['Accept'] = "text/plain";
        unset($this->headers['Content-Type']);
        $url = "projects/{$this->project_id}/tasks/$task_id";
        return $this->apiCall(self::DELETE, $url);
    }

    public function deleteSchedule($project_id, $schedule_id){
        $this->setProjectId($project_id);
        $url = "projects/{$this->project_id}/schedules/$schedule_id";

        $request = array(
            'schedule_id' => $schedule_id
        );

        return $this->apiCall(self::DELETE, $url, $request);
    }

    public function getSchedules($project_id){
        $this->setProjectId($project_id);
        $this->setJsonHeaders();
        $url = "projects/{$this->project_id}/schedules";
        $schedules = json_decode($this->apiCall(self::GET, $url));
        return $schedules->schedules;
    }

    /**
     * @param string $project_id
     * @param string $name
     * @param int $delay delay in seconds
     * @return string posted Schedule id
     */
    public function postScheduleSimple($project_id, $name, $delay = 1){
        return $this->postSchedule($project_id, $name, array('delay' => $delay));
    }

    /**
     * @param string $project_id
     * @param string $name
     * @param string $start_at Time of first run.
     * @param int $run_every Time in seconds between runs. If omitted, task will only run once.
     * @param string $end_at Time tasks will stop being enqueued.
     * @param int $run_times Number of times to run task.
     * @param int $priority Priority queue to run the job in (0, 1, 2). p0 is default.
     * @return string posted Schedule id
     */
    public function postScheduleAdvanced($project_id, $name, $start_at, $run_every = null, $end_at = null, $run_times = null, $priority = null){
        $options = array();
        $options['start_at'] = $start_at;
        if (!empty($run_every)) $options['run_every'] = $run_every;
        if (!empty($end_at))    $options['end_at']    = $end_at;
        if (!empty($run_times)) $options['run_times'] = $run_times;
        if (!empty($priority))  $options['priority']  = $priority;
        return $this->postSchedule($project_id, $name, $options);
    }

    public function postTask($project_id, $name, $payload = array()){
        $this->setProjectId($project_id);
        $url = "projects/{$this->project_id}/tasks";

        $request = array(
            'tasks' => array(
                array(
                    "name"      => $name,
                    "code_name" => $name,
                    'payload' => json_encode($payload),
                )
            )
        );

        $this->setCommonHeaders();
        $res = $this->apiCall(self::POST, $url, $request);
        #$this->debug('postTask res', $res);
        $tasks = json_decode($res);
        return $tasks->tasks[0]->id;
    }

    public function getLog($project_id, $task_id){
        $this->setProjectId($project_id);
        if (empty($task_id)){
            throw new InvalidArgumentException("Please set task_id");
        }
        $this->setJsonHeaders();
        $url = "projects/{$this->project_id}/tasks/$task_id/log";
        $this->headers['Accept'] = "text/plain";
        unset($this->headers['Content-Type']);
        return $this->apiCall(self::GET, $url);
    }

    public function getTaskDetails($project_id, $task_id){
        $this->setProjectId($project_id);
        if (empty($task_id)){
            throw new InvalidArgumentException("Please set task_id");
        }
        $this->setJsonHeaders();
        $url = "projects/{$this->project_id}/tasks/$task_id";
        return json_decode($this->apiCall(self::GET, $url));
    }


    public function cancelTask($project_id, $task_id){
        $this->setProjectId($project_id);
        if (empty($task_id)){
            throw new InvalidArgumentException("Please set task_id");
        }
        $url = "projects/{$this->project_id}/tasks/$task_id/cancel";
        $request = array();

        $this->setCommonHeaders();
        $res = $this->apiCall(self::POST, $url, $request);
        $responce = json_decode($res);
        return $responce;
    }

    public function setTaskProgress($project_id, $task_id, $percent, $msg = ''){
        $this->setProjectId($project_id);
        if (empty($task_id)){
            throw new InvalidArgumentException("Please set task_id");
        }
        $url = "projects/{$this->project_id}/tasks/$task_id/progress";
        $request = array(
            'percent' => $percent,
            'msg'     => $msg
        );

        $this->setCommonHeaders();
        $res = $this->apiCall(self::POST, $url, $request);
        $responce = json_decode($res);
        return $responce;
    }

    /* PRIVATE FUNCTIONS */

    /**
     *
     * @param string $project_id
     * @param string $name
     * @param array $options options contain:
     *   start_at OR delay — required - start_at is time of first run. Delay is number of seconds to wait before starting.
     *   run_every         — optional - Time in seconds between runs. If omitted, task will only run once.
     *   end_at            — optional - Time tasks will stop being enqueued. (Should be a Time or DateTime object.)
     *   run_times         — optional - Number of times to run task. For example, if run_times: is 5, the task will run 5 times.
     *   priority          — optional - Priority queue to run the job in (0, 1, 2). p0 is default. Run at higher priorities to reduce time jobs may spend in the queue once they come off schedule. Same as priority when queuing up a task.
     * @param array $payload
     * @return mixed
     */
    private function postSchedule($project_id, $name, $options, $payload = array()){

        $this->setProjectId($project_id);
        $url = "projects/{$this->project_id}/schedules";

        $shedule = array(
           'name' => $name,
           'code_name' => $name,
           'payload' => json_encode($payload),
        );
        $request = array(
           'schedules' => array(
               array_merge($shedule, $options)
           )
        );

        $this->setCommonHeaders();
        $res = $this->apiCall(self::POST, $url, $request);
        $shedules = json_decode($res);
        return $shedules->schedules[0]->id;
    }

    private function compiledHeaders(){

        # Set default headers if no headers set.
        if ($this->headers == null){
            $this->setCommonHeaders();
        }

        $headers = array();
        foreach ($this->headers as $k => $v){
            $headers[] = "$k: $v";
        }
        return $headers;
    }

    private function runtimeFileType($name) {
        if(empty($name)){
            return false;
        }
        $explodedName= explode(".", $name);
        switch ($explodedName[(count($explodedName)-1)]) {
            case "php":
                return "php";
            case "rb":
                return "ruby";
            case "py":
                return "python";
            case "javascript":
                return "javascript";
            default:
                return "no_type_found";
        }
    }

    private function apiCall($type, $url, $params = array()){
        $url = "{$this->url}$url";

        $s = curl_init();
        if (! isset($params['oauth'])) {
          $params['oauth'] = $this->token;
        }
        switch ($type) {
            case self::DELETE:
                $fullUrl = $url . '?' . http_build_query($params);
                $this->debug('apiCall fullUrl', $fullUrl);
                curl_setopt($s, CURLOPT_URL, $fullUrl);
                curl_setopt($s, CURLOPT_CUSTOMREQUEST, self::DELETE);
                break;
            case self::POST:
                $this->debug('apiCall url', $url);
                curl_setopt($s, CURLOPT_URL,  $url);
                curl_setopt($s, CURLOPT_POST, true);
                curl_setopt($s, CURLOPT_POSTFIELDS, json_encode($params));
                break;
            case self::GET:
                $fullUrl = $url . '?' . http_build_query($params);
                $this->debug('apiCall fullUrl', $fullUrl);
                curl_setopt($s, CURLOPT_URL, $fullUrl);
                break;
        }

        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($s, CURLOPT_HTTPHEADER, $this->compiledHeaders());
        $_out = curl_exec($s);
        $status = curl_getinfo($s, CURLINFO_HTTP_CODE);
        curl_close($s);
        switch ($status) {
            case self::HTTP_OK:
            case self::HTTP_CREATED:
            case self::HTTP_ACEPTED:
                $out = $_out;
                break;
            default:
                throw new Http_Exception("http error: {$status} | {$_out}", $status);
        }
        return $out;
    }


    /**
     * @param array|string $config_file_or_options
     * array of options or name of config file
     * @return array
     * @throws InvalidArgumentException
     */
    private function getConfigData($config_file_or_options){
        if (is_string($config_file_or_options)){
            $ini = parse_ini_file($config_file_or_options, true);
            if ($ini === false){
                throw new InvalidArgumentException("Config file $config_file_or_options not found");
            }
            if (empty($ini['simple_worker'])){
                throw new InvalidArgumentException("Config file $config_file_or_options has no section 'simple_worker'");
            }
            $config =  $ini['simple_worker'];
        }elseif(is_array($config_file_or_options)){
            $config = $config_file_or_options;
        }else{
            throw new InvalidArgumentException("Wrong parameter type");
        }
        foreach ($this->required_config_fields as $field){
            if (empty($config[$field])){
                throw new InvalidArgumentException("Required config key missing: '$field'");
            }
        }
        return $config;
    }

    private function getFileContent($filename){
        return file_get_contents($filename);
    }

    private function setCommonHeaders(){
        $this->headers = array(
            'Authorization'   => "OAuth {$this->token}",
            'User-Agent'      => self::header_user_agent,
            'Content-Type'    => 'application/json',
            'Accept'          => self::header_accept,
            'Accept-Encoding' => self::header_accept_encoding
        );
    }

    private function setJsonHeaders(){
        $this->setCommonHeaders();
    }

    private function setPostHeaders(){
        $this->setCommonHeaders();
        $this->headers['Content-Type'] ='multipart/form-data';
    }

    private function debug($var_name, $variable){
        if ($this->debug_enabled){
            echo "{$var_name}: ".var_export($variable,true)."\n";
        }
    }

    private static function fileNamesRecursive($dir, $base_dir = ''){
        $dir .= DIRECTORY_SEPARATOR;
        $files = scandir($dir);
        $names = array();

        foreach($files as $name){
             if ($name == '.' || $name == '..' || $name == '.svn') continue;
             if (is_dir($dir.$name)){
                 $inner_names = self::fileNamesRecursive($dir.$name, $base_dir.$name.'/');
                 foreach ($inner_names as $iname){
                     $names[] = $iname;
                 }
             }else{
                 $names[] = $base_dir.$name;
             }
        }
        return $names;
    }
}
