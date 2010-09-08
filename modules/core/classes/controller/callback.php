<?php


class Controller_Callback  extends Controller {


    public function action_ajax($callback) {
        $arr = Kohana::config('callbacks.'.$callback);
        
        $result = call_user_func_array($arr, $_GET);
        if (is_array($result)) {
            if (count($result) > 0 && $result[0] instanceof Record_Base) {
                $tmp = array();
                foreach ($result as &$record) {
                    if ($record instanceof Record_Base) {
                        $record = $record->as_array();
                    }
                }
            }
            $result = json_encode($result);
        } else if ($result instanceof Record_Base) {
            $result = json_encode($result->as_array());
        }
        $this->request->response = $result;
    }

}