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
    const header_accept_encoding = "gzip, functionlate";
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACEPTED = 202;

    const POST   = 'POST';
    const GET    = 'GET';
    const DELETE = 'DELETE';

    function __construct($token, $host = "174.129.54.171", $port='8080', $version='2', $protocol='http')
    {
        //$this->url = "https://worker-aws-us-east-1.iron.io/2/";
        $this->url = "$protocol://$host:$port/$version/";
        echo "url = " . $this->url . "\n";
        $this->token = $token;
        $this->project_id = '';
    }

    public static function create_zip($files = array(), $destination = '',$overwrite = false) {
        //if the zip file already exists and overwrite is false, return false
        if(file_exists($destination) && !$overwrite) { return false; }
        //vars
        $valid_files = array();
        //if files were passed in...
        if(is_array($files)) {
            //cycle through each file
            foreach($files as $file) {
                //make sure the file exists
                if(file_exists($file)) {
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
                $zip->addFile($file,$file);
            }
            $zip->close();
            return file_exists($destination);
        }
        else
        {
            return false;
        }
    }

    function setJsonHeaders(){
        $this->headers = array();
        $this->headers['User-Agent'] =self::header_user_agent;
        $this->headers['Accept'] = self::header_accept;
        $this->headers['Accept-Encoding'] =self::header_accept_encoding;
    }
    function setPostHeaders(){
        $this->headers = array();
        $this->headers['User-Agent'] =self::header_user_agent;
        $this->headers['Accept'] = self::header_accept;
        $this->headers['Accept-Encoding'] =self::header_accept_encoding;
        $this->headers['Content-Type'] ='multipart/form-data';
    }


    function setProjectId($project_id) {
        if ($project_id != ''){
          $this->project_id = $project_id;
        }
    }
    private function runtimeFileType($name) {
        if($name ==""){
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
    private function apiCall($type, $url, $params = array())
    {
        $url = $this->url.$url;
        $params['oauth']= $this->token;
        $headers = $this->headers;
        $s = curl_init();
        switch ($type) {
            case self::DELETE:
                curl_setopt($s, CURLOPT_URL, $url . '?' . http_build_query($params));
                curl_setopt($s, CURLOPT_CUSTOMREQUEST, self::DELETE);
                break;
            case self::POST:
                curl_setopt($s, CURLOPT_URL,  $url.'?oauth=' . $this->token);
                curl_setopt($s, CURLOPT_POST, true);
                curl_setopt($s, CURLOPT_POSTFIELDS, $params);
                break;
            case self::GET:
                curl_setopt($s, CURLOPT_URL, $url . '?' . http_build_query($params));
                break;
        }
    
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
        $_out = curl_exec($s);
        $status = curl_getinfo($s, CURLINFO_HTTP_CODE);
        print_r($_out);
        curl_close($s);
        switch ($status) {
            case self::HTTP_OK:
            case self::HTTP_CREATED:
            case self::HTTP_ACEPTED:
                $out = $_out;
                break;
            default:
                throw new Http_Exception("http error: {$status}", $status);
        }
        return $out;
    }
    function getProjects(){
        $this->setJsonHeaders();
        $projects = json_decode($this->apiCall(self::GET, 'projects'));
        return $projects->projects;
    }
    function getTasks($project_id = ''){
        $this->setProjectId($project_id);
        $url = 'projects/'.$this->project_id.'/tasks';
        $this->setJsonHeaders();
        $task = json_decode($this->apiCall(self::GET, $url));
        return $tasks->tasks;
    }
    function getProjectDetails($project_id = ''){
        $this->setProjectId($project_id);
        $this->setJsonHeaders();
        $url =  'projects/'+$this->project_id;
        return json_decode($this->apiCall(self::GET, $url));
    }
    function getCodes($project_id = ''){
        $this->setProjectId($project_id);
        $this->setJsonHeaders();
        $url = 'projects/'.$this->project_id.'/codes';
        $codes = json_decode($this->apiCall(self::GET, $url));
        return $codes->codes;
    }
    function getCodeDetails($code_id, $project_id = ''){
        $this->setProjectId($project_id);   
        $this->setJsonHeaders();
        $url = 'projects/'.$this->project_id.'/codes/'.$code_id;
        return json_decode(json_decode($this->apiCall(self::GET, $url)));
    }

    function getFileContent($filename)
    {
      echo "filename = " . $filename . "\n";
      $fn = getcwd() . "/" . $filename;
      echo "filename = " . $fn . "\n";
      $fh = fopen($fn, "rb");
      $contents = fread($fh, filesize($fn));
      fclose($fh);
      return $contents;
    }

    function postCode($project_id, $filename, $zipFilename,$name){
        $this->setProjectId($project_id);
        $url =  'projects/'.$this->project_id.'/codes';
        $this->setPostHeaders();
        $this->headers['Content-Length'] = filesize($zipFilename);
        $ts = time();
        $runtime_type = $this->runtimeFileType($filename);
        $sendingData = array("code_name" => $name, "name" => $name,"standalone" => True,"runtime" => $runtime_type, "file_name" => $filename,"version" => $this->version,"timestamp" => $ts,"oauth" => $this->token, "class_name" => $name, "options" => array(), "access_key" => $name);
        //$sendingData = array();
        //$sendingData[] = json_encode($sendingData);
        $sendingData = json_encode($sendingData);
        //print_r($sendingData);exit;
        // For reference to multi-part encoding in php, see:
        //    http://vedovini.net/2009/08/posting-multipart-form-data-using-php/
        $eol = "\r\n";
        $data = '';
        $mime_boundary = md5(time());
        $data .= '--' . $mime_boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="data"' . $eol . $eol;
        //$data .= "Some Data" . $eol;
        $data .= $sendingData . $eol;
        $data .= '--' . $mime_boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="file"; filename=$zipFilename' . $eol;
        $data .= 'Content-Type: text/plain' . $eol;
        $data .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
        $fileContent = $this->getFileContent($zipFilename);
        //$data .= chunk_split(base64_encode("Some file content")) . $eol;
        $data .= chunk_split(base64_encode($fileContent)) . $eol;
        $data .= "--" . $mime_boundary . "--" . $eol . $eol; // finish with two eol's!!
 
        $params = array('http' => array(
                  'method' => 'POST',
                  'header' => 'Content-Type: multipart/form-data; boundary=' . $mime_boundary . $eol,
                  'content' => $data
               ));

        //print_r($params);exit(); 
        $ctx = stream_context_create($params);
        //$response = @file_get_contents($destination, FILE_TEXT, $ctx);
        $destination = $this->url . "projects/$project_id/codes?oauth=".$this->token;
        //print_r($destination); echo "\n"; exit();
        echo "\n destination:  $destination \n";
        //$response = @file_get_contents($destination, FILE_TEXT, $ctx);
        $response = file_get_contents($destination, false, $ctx);
        echo "\n Response of file_get_contents:  \n";
        echo serialize($response) . "\n";
        print_r($response);echo "\n";exit();
        //$sendingData["file"] = '@'.$zipFilename;
        //$sendingData = json_encode($sendingData);
        //echo "\n sendingData :  \n";
        //print_r($sendingData);echo "\n";
        //$body = $this->apiCall(self::POST, $url,$sendingData);
        //$body = $this->apiCall(self::POST, $url, $data);
        //print_r($body);echo "\n";
        //return json_decode($body);
    }

 /*
    function postProject($name){
        $url =  'projects';
        payload = [{"name" : $name, "class_name" : $name, "access_key" : $name}]
        timestamp = time.asctime()
        #data = {"payload" : payload, "oauth" : $this->token, "version" : $this->version, "timestamp" : timestamp, "options" : {}, "api_version" : $this->version
    
        #data = {"payload" : payload}
        data = {"name" : name}
        data = json.dumps(data)
        dataLen = len(data)
        headers = $this->headers
        headers['Content-Type'] = "application/json"
        headers['Content-Length'] = str(dataLen)
    
        req = urllib2.Request(url, data, headers)
        ret = urllib2.urlopen(req)
        s = ret.read()
        echo "postProject returns:  " + s
        msg = json_decode(s)
        project_id = msg['id']
        return project_id
    }
*/
    function deleteProject($project_id){
        $this->setProjectId($project_id);
        $url = 'projects/'.$this->project_id;
        return $this->apiCall(self::DELETE, $url);
     }

    function deleteCode($project_id, $code_id){
        $this->setProjectId($project_id);
        $url = 'projects/'.$this->project_id.'/codes/'.$code_id;
        return $this->apiCall(self::DELETE, $url);
    }

    function deleteTask($project_id, $task_id){
        $this->setProjectId($project_id);
        $url = 'projects/'.$this->project_id.'/tasks/'+$task_id;
        return $this->apiCall(self::DELETE, $url);
    }

    function deleteSchedule($project_id, $schedule_id){
        $this->setProjectId($project_id);
        $url = 'projects/'.$project_id.'/schedules/'.$schedule_id;
        return $this->apiCall(self::DELETE, $url);
    }

    function getSchedules($project_id){
        $this->setProjectId($project_id);
        $this->setJsonHeaders();
        $url = 'projects/'.$this->project_id.'/schedules';
        $schedules = json_decode(json_decode($this->apiCall(self::GET, $url)));
        return $schedules->schedules;
    }
 /*
    function postSchedule($project_id, $name, $delay){
        # hash_to_send["payload"] = data
        # hash_to_send["class_name"] = class_name
        # hash_to_send["schedule"] = schedule - this is a hash too
    
        #delay = delay + int(time.time() + 0.5)
        #dt = datetime.fromtimestamp(delay + int(time.time()))
        #delay = dt.isoformat()
        #delay = time.asctime(time.gmtime(delay))
        #delay = (time.time() + delay) * 1.0e9
        #delay = (time.time() + delay)
        #delay = int(delay)
        echo "delay = " + str(delay)
        #delay = time.gmtime(delay)
    
        if project_id == '':
          project_id = $this->project_id
        url = $this->url + 'projects/'+project_id+'/schedules?oauth=' + $this->token
        echo "postSchedule url:  " + url
        timestamp = time.asctime()
        
        #schedule = {"delay" : delay, "project_id" : project_id}
        schedule = {"delay" : delay, "code_name" : name}
        payload = {"schedule" : schedule, "project_id" : project_id, "class_name" : name, "name" : name, "options" : "{}", "token" : $this->token, "api_version" : $this->version , "version" : $this->version, "timestamp" : timestamp, "oauth" : $this->token, "access_key" : name, "delay" : delay}
        options = {"project_id" : project_id, "schedule" : schedule, "class_name" : name, "name" : name, "options" : "{}", "token" : $this->token, "api_version" : $this->version , "version" : $this->version, "timestamp" : timestamp, "oauth" : $this->token, "access_key" : name, "delay" : delay}
        data = {"project_id" : project_id, "schedule" : schedule, "class_name" : name, "name" : name, "options" : options, "token" : $this->token, "api_version" : $this->version , "version" : $this->version, "timestamp" : timestamp, "oauth" : $this->token, "access_key" : name, "delay" : delay , "payload" : payload}
    
        payload = [{"class_name" : name, "access_key" : name, "name" : name}]
        data =  {"name" : name, "delay" : delay, "payload" : payload}
        #data = json.dumps(data)
        schedules = [schedule]
        data = {"schedules" : schedules}
        data = json.dumps(data)
        echo "data = " + data
        dataLen = len(data)
        headers = $this->headers
        headers['Content-Type'] = "application/json"
        headers['Content-Length'] = str(dataLen)
        headers['Accept'] = "application/json"
        req = urllib2.Request(url, data, headers)
        ret = urllib2.urlopen(req)
        s = ret.read()
        echo "post schedules returns:  " + s
        # post schedules returns:  {"msg":"Scheduled","schedules":[{"id":"4ea35d11cddb1344fe00000c"}],"status_code":200}
    
        msg = json_decode(s)
        schedule_id = msg['schedules'][0]['id']
        return schedule_id
    }
    function postTask($project_id, $name){
        if project_id == '':
          project_id = $this->project_id
        url = $this->url + 'projects/'+project_id+'/tasks?oauth=' + $this->token
        echo "postTask url:  " + url
        payload = [{"class_name" : name, "access_key" : name, "code_name" : name}]
        timestamp = time.asctime()
        data = {"code_name" : name, "payload" : payload, "class_name" : name, "name" : name, "options" : "{}", "token" : $this->token, "api_version" : $this->version , "version" : $this->version, "timestamp" : timestamp, "oauth" : $this->token, "access_key" : name}
        #task = {"code_name" : name, "payload" : payload, "priority" : 0, "timeout" : 3600}
        payload = json.dumps(payload)
        task = {"name" : name, "code_name" : name, "payload" : payload}
        tasks = {"tasks" : [task]}
        data = json.dumps(tasks)
        echo "postTasks, data = " + data
        dataLen = len(data)
        headers = $this->headers
        headers['Content-Type'] = "application/json"
        headers['Content-Length'] = str(dataLen)
    
        req = urllib2.Request(url, data, headers)
        ret = urllib2.urlopen(req)
        s = ret.read()
        echo "postTasks returns:  " + s
        # postTasks returns:  {"msg":"Queued up","status_code":200,"tasks":[{"id":"4ea35c4fcddb1344fe000007"}]}
    
        ret = json_decode(s)
        return ret
    }
*/
    function getLog($project_id, $task_id){
        $this->setProjectId($project_id);
        $this->setJsonHeaders();
        $url = $this->url . 'projects/' .$project_id . '/tasks/'.task_id.'/log/';
        $this->headers['Accept'] = "text/plain";
        unset($this->headers['Content-Type']);
        return json_decode(json_decode($this->apiCall(self::GET, $url)));
    }


}
