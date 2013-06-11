<?php
	require("subList.php");
	require("recordMap.php");
	
	// clean the output, then make it Web SAPI compatible
	
	if(PHP_SAPI != "cli") 	// make sure we're in CLI for now
	{
		echo "\n[!] This is a command-line tool for now! Please run from CLI [attempted environment: '" . PHP_SAPI . "']\n";
		exit;
	}
	
	if($argc < 2)
	{
		echo "\n[!] Please supply a base domain and TLD; e.g: dnsMapper.php targetDomain.org\n";
		exit;
	}
	
	if(!function_exists("dns_get_record") || !function_exists("checkdnsrr"))
	{
		echo "\n[!] Essential(missing) DNS functions are required for this script to work.\nBIND will need to be running for Linux users.\n";
		exit;
	}
	
	$target		= $argv[1]; // target domain
	$targetRecords  = dns_get_record($target, DNS_ALL);
	
	if(!$targetRecords)
	{
		echo "\n[!] Error resolving target domain: '$target' - Please verify the target is a valid, resolvable domain.\n";
		exit;
	}
		
	// begin mapping
	echo "<!-- dnsMapper.php v1.3 - lb -->\n";
	
	//------ DNS_ALL
	//echo "\n<!-- Attempting to map hosts from available records on '" . $target . "' -->\n";
	
	//recordMap($targetRecords, 1, $target);

	// ----- STATIC LIST
	echo "\n<!-- Attempting to map hosts from static list on '" . $target . "' -->\n";
	
	$c = 0;				// number of subDomains checked
	$n = count($subDomainList);	// total number of subDomains
	$r = 0;				// number of consecutively successful resolves
	
	echo "\n<!-- Processing $n total subdomains, estimated completion time: " . round(($n * 2.5) / 60, 2) . " minutes [apprx. ".round($n / 60, 0)." domains/min]  -->\n";
	
	foreach($subDomainList as $subDomain)
	{
		$c++;
		
		if($r > 4)
		{
			echo "\n<!-- [!] Warning: many consecutive successful resolves, target may have wildcard record(s)! -->\n";
			$r = 0; 	// suppress warning scatter
		}
		
		$valid = checkdnsrr($subDomain . "." . $target, "A");
		
		if(!$valid)
		{
			$r = 0;		// reset consecutive resolution counter
		}
		else
		{
			$r++;		// increment consecutive resolution counter
			$valid = dns_get_record($subDomain.".".$target, DNS_ALL);
			if(!$valid)
			{
				echo "\n<!-- [!] Warning: dns_get_record FAILED to resolve valid(?) subhost: " . $subDomain . "." . $target . ", skipping -->\n";
			}
			else
			{
				recordMap($valid, 1, $subDomain.".".$target);
			}
		}
		
		sleep(2);
	}
?>