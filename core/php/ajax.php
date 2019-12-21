<?php
    $responses = array();
        
    $requests = json_decode(file_get_contents("php://input"));
    
    foreach ($requests as $request) {
        $response = new stdClass();
        $response->tid = $request->tid;
        
        switch ($request->facadeFn) {
            case 'translate.load':
                try {
                    $response->formData = array(
                        'operation'=>'js-babel-min'
                    );
                } catch (Exception $ex) {
                    $response->errorMsg = $ex->getMessage();
                }
                break;
            
            case 'translate.minify':
                try {
                    $data = $request->data->input;
                    
                    switch ($request->data->operation) {
                        case 'js-min';
                        case 'js-babel-min';
                            require_once '../lib/jsmin/JSMin.php';
                            $data = JSMin::minify($data);
                            break;
                        
                        case 'css-less';
                        case 'css-less-min';
                        case 'css-min';
                            // LESS
                            if (strpos($request->data->operation, 'less') !== false) {
                                require_once '../lib/lessphp/lessc.inc.php';
                                $less = new lessc;
                                $data = $less->compile($data);
                            }
                            
                            // MIN
                            if (strpos($request->data->operation, 'min') !== false) {
                                require_once '../lib/cssmin/cssmin.php';
                                $data = cssmin::minify($data);
                                break;
                            }
                            break;
                            
                    }
                    
                    $response->data = $data;
                    
                } catch (Exception $ex) {
                    $response->errorMsg = $ex->getMessage();
                }
                break;
            
            default:
                $response->errorMsg = 'FacadeFn "' . $request->facadeFn . '" existiert nicht.';
        }
        
        $responses[] = $response;
    }
    
    print(json_encode($responses));
    
    