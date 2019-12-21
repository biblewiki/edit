<?php
    header('Pragma: private');
    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0');
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . filesize(utf8_decode('../compress.php')));
    header('Content-Disposition: attachment; filename="'.utf8_decode('compress.php').'"');

    readfile(utf8_decode('../compress.php'));