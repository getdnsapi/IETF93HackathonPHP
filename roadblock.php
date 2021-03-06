<html><body>
Enter an address for a publicly resolver here:
<form><input type="text" name="addr"><input type="submit"></form>
<font size="-1">Private resolvers can not be assessed with this web application.</font><br><br>
<?php

if (!$_REQUEST["addr"]) {
	$success = True;
	goto error;
}
$success = False;

$context = 0;
if (php_getdns_context_create($context, True)) {
	goto error;
}

if (php_getdns_context_set_resolution_type($context, GETDNS_RESOLUTION_STUB)) {
	goto error_destroy_context;
}

$txt_addr =  $_REQUEST["addr"] ? $_REQUEST["addr"] : "8.8.8.8";
$addr = inet_pton($txt_addr);

print("Results for $txt_addr:<br><br>");

$upstreamsArr = array(
	0 => array("address_data" => $addr,
		   "address_type" => strlen($addr) == 4 ? "IPv4" : "IPv6")
	);

$upstreams = 0;
if (php_getdns_util_convert_array($upstreamsArr, $upstreams)) {
	goto error_destroy_context;
}

if (php_getdns_context_set_upstream_recursive_servers($context, $upstreams)) {
	goto error_destroy_upstreams;
}

$extensionsArr = array( "dnssec_return_status" => GETDNS_EXTENSION_TRUE
                      , "dnssec_return_validation_chain" => GETDNS_EXTENSION_TRUE);
$extensions = 0;
if (php_getdns_util_convert_array($extensionsArr, $extensions)) {
	goto error_destroy_upstreams;
}

$pgrade = 0;
$pfail = False;
$grade = 0;
$respDict = 0;
if (php_getdns_general_sync($context,
    "alg-8-nsec3.dnssec-test.org", GETDNS_RRTYPE_SOA, $extensions, $respDict)) {
	goto error_destroy_upstreams;
}
$respArr = array();
if (php_getdns_util_convert_dict($respDict, $respArr)) {
	goto error_destroy_response;
}
if ($respArr["replies_tree"][0]["header"]["ancount"] > 0) {
	$grade += 1;
	print("<font color=\"green\">Query for alg-8-nsec3.dnssec-test.org returned answers</font>: $grade<br>");
} else {
	print("<font color=\"red\">Query for alg-8-nsec3.dnssec-test.org returned no answers</font>: $grade<br>");
}
if ($respArr["replies_tree"][0]["dnssec_status"] == GETDNS_DNSSEC_SECURE) {
	$grade += 1;
	if (!$pfail) { $pgrade += 1; }
	print("<font color=\"green\">Query for alg-8-nsec3.dnssec-test.org had secure answer</font>: $grade<br>");
} else {
	$pfail = True;
	print("<font color=\"red\">Query for alg-8-nsec3.dnssec-test.org did not have an secure answer</font>: $grade<br>");
}

php_getdns_dict_destroy($respDict);
$respDict = 0;
if (php_getdns_general_sync($context,
    "realy-doesnotexist.dnssec-test.org.", GETDNS_RRTYPE_A, $extensions, $respDict)) {
	goto error_destroy_upstreams;
}
$respArr = array();
if (php_getdns_util_convert_dict($respDict, $respArr)) {
	goto error_destroy_response;
}
if ($respArr["replies_tree"][0]["header"]["ancount"] == 0) {
	$grade += 1;
	print("<font color=\"green\">Query for realy-doesnotexist.dnssec-test.org. did not return answers</font>: $grade<br>");
} else {
	print("<font color=\"red\">Query for realy-doesnotexist.dnssec-test.org. did return answers</font>: $grade<br>");
}
if ($respArr["replies_tree"][0]["dnssec_status"] == GETDNS_DNSSEC_SECURE) {
	$grade += 1;
	if (!$pfail) { $pgrade += 1; }
	print("<font color=\"green\">Query for realy-doesnotexist.dnssec-test.org. was secure</font>: $grade<br>");
} else {
	$pfail = True;
	print("<font color=\"red\">Query for realy-doesnotexist.dnssec-test.org. was not secure</font>: $grade<br>");
}
php_getdns_dict_destroy($respDict);
$respDict = 0;
php_getdns_context_set_edns_do_bit($context, True);
if (php_getdns_general_sync($context,
    "dnssec-failed.org", GETDNS_RRTYPE_SOA, NULL, $respDict)) {
	goto error_destroy_upstreams;
}
$respArr = array();
if (php_getdns_util_convert_dict($respDict, $respArr)) {
	goto error_destroy_response;
}
if ($respArr["replies_tree"][0]["header"]["ancount"] == 0) {
	$grade += 1;
	print("<font color=\"green\">Query for dnssec-failed.org returned no answers</font>: $grade<br>");
} else {
	print("<font color=\"red\">Query for dnssec-failed.org returned answers</font>: $grade<br>");
}
if ($respArr["replies_tree"][0]["header"]["rcode"] == GETDNS_RCODE_SERVFAIL
    && $respArr["replies_tree"][0]["header"]["ad"] == 0) {
	$grade += 1;
	if (!$pfail) { $pgrade += 1; }
	print("<font color=\"green\">rcode for dnssec-failed.org was SERVFAIL</font>: $grade<br>");
} else {
	$pfail = True;
	print("<font color=\"red\">rcode for dnssec-failed.org was not SERVFAIL</font>: $grade<br>");
}

$respDict = 0;
if (php_getdns_general_sync($context,
    "alg-13-nsec3.dnssec-test.org", GETDNS_RRTYPE_SOA, $extensions, $respDict)) {
	goto error_destroy_upstreams;
}
$respArr = array();
if (php_getdns_util_convert_dict($respDict, $respArr)) {
	goto error_destroy_response;
}
if ($respArr["replies_tree"][0]["header"]["ancount"] > 0) {
	$grade += 1;
	print("<font color=\"green\">Query for alg-13-nsec3.dnssec-test.org returned answers</font>: $grade<br>");
} else {
	print("<font color=\"red\">Query for alg-13-nsec3.dnssec-test.org returned no answers</font>: $grade<br>");
}
if ($respArr["replies_tree"][0]["dnssec_status"] == GETDNS_DNSSEC_SECURE) {
	$grade += 1;
	print("<font color=\"green\">Query for alg-13-nsec3.dnssec-test.org had secure answer</font>: $grade<br>");
} else {
	print("<font color=\"red\">Query for alg-13-nsec3.dnssec-test.org did not have an secure answer</font>: $grade<br>");
}


$success = True;

error_destroy_response:
	php_getdns_dict_destroy($respDict);
error_destroy_upstreams:
	php_getdns_list_destroy($upstreams);
error_destroy_context:
	php_getdns_context_destroy($context);


printf("<img width=\"500\" src=\"dial$pgrade.png\">");
error:
?>
<br>
Also try:
<table>
<tr><td>DNS Advantage</td><td><a href="?addr=156.154.70.1">156.154.70.1</a></td><td><a href="?addr=156.154.71.1">156.154.71.1</a></td></tr>
<tr><td>Dyn Internet Guide</td><td><a href="?addr=216.146.35.35">216.146.35.35</a></td><td><a href="?addr=216.146.36.36">216.146.36.36</a></td></tr>
<tr><td>Google</td><td><a href="?addr=8.8.8.8">8.8.8.8</a></td><td><a href="?addr=8.8.4.4">8.8.4.4</a></td></tr>
<tr><td>Level 3</td><td><a href="?addr=209.244.0.3">209.244.0.3</a></td><td><a href="?addr=209.244.0.4">209.244.0.4</a></td></tr>
<tr><td>OpenDNS Home</td><td><a href="?addr=208.67.222.222">208.67.222.222</a></td><td><a href="?addr=208.67.220.220">208.67.220.220</a></td></tr>
<tr><td>Verisign</td><td><a href="?addr=198.41.2.2">198.41.2.2</a></td><td><a href="?addr=198.41.1.1">198.41.1.1</a></td></tr>
</table><dl>
<dt>Github:</dt><dd><a href="https://github.com/getdnsapi/IETF93HackathonPHP">https://github.com/getdnsapi/IETF93HackathonPHP</a></dd>
<dt>Hackathon page:</dt><dd><a href="https://www.ietf.org/registration/MeetingWiki/wiki/dnsresolvercapabilities">https://www.ietf.org/registration/MeetingWiki/wiki/dnsresolvercapabilities</a></dd>
<dt>Source:</dt><dd style="background-color: #eee; border: 1px solid black; padding: 1ex"><?php 

	show_source(__FILE__);   

?></dd></dl></body></html>
<?php
	exit($success ? 0 : -1);

