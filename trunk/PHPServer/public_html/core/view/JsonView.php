<?php

Class JsonView implements iView
{
    public function render(array $data)
    {
        $json = json_encode($data);

        $callback = RequestData::GetRequestData('callback');
        //start output
        if ($callback) {
            header('Content-Type: text/javascript');
            echo $callback . '(' . $json . ');';
        } else {
            header('Content-Type: application/x-json');
            echo $json;
        }
    }
}

?>

