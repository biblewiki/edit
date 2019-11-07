<?php

const HOST_DOMAIN = '';
const EDIT_HOST = '';
const LOGIN_HOST = '';
const SCRIPT_URL = '';
const SCRIPT_PATH = '';

// Domain without https:// and Subdomain !!!
define(HOST_DOMAIN, "biblewiki.one");

// Edit page address
define(EDIT_HOST, "https://edit.joel.biblewiki.one");

// Login page address
define(LOGIN_HOST, "https://login.biblewiki.one");

// Scripts URL
define(SCRIPT_URL, "https://www.joel.biblewiki.one/sources");

// Scripts Path
$user = posix_getpwuid(posix_getuid());
$homedir = $user['dir'];
define(SCRIPT_PATH, $homedir . "/www/biblewiki.one/joel/www/sources");