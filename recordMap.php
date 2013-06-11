<?php 
	function recordMap($targetRecords, $recursionLevel, $domain = "")
	{
		if($recursionLevel > 5)
		{
			echo "\n<!-- [!] Recursive loop detected, pedaling backward! -->\n";
			return;
		}
		
		sleep(2);
		foreach($targetRecords as $targetRecord)
		{
			switch($targetRecord['type'])
			{
				case "A":
					echo "\n".str_repeat("\t", $recursionLevel)."" . $targetRecord['ip'] . "\t<!-- $domain -->";
				break;

				case "NS":
					echo "\n".str_repeat("\t", $recursionLevel)."" . $targetRecord['target'] . "\t<!-- NS -->";
					recordMap(dns_get_record($targetRecord['target'], DNS_ALL), ($recursionLevel + 1), $targetRecord['target']);
				break;
				
				case "SOA":
					echo "\n".str_repeat("\t", $recursionLevel)."".$targetRecord['mname']."\t<!-- SOA:".$targetRecord['rname']." -->";
					recordMap(dns_get_record($targetRecord['mname'], DNS_ALL), ($recursionLevel + 1), $targetRecord['mname']);
				break;
				
				case "MX":
					echo "\n".str_repeat("\t", $recursionLevel)."".$targetRecord['target']."\t<!-- MX -->";
					recordMap(dns_get_record($targetRecord['target'], DNS_ALL), ($recursionLevel + 1), $targetRecord['target']);
				break;
				
				case "TXT":
					// extrapolate hosts from txt if possible
					/*$txt  = $targetRecord['txt'];
					$host = $targetRecord['host'];
					
					if(preg_match("/:.*?\x20/", $txt, $match) != 0)
					{
						$match[0] = substr($match[0], 1);
						$match[0] = str_replace("_","", $match[0]);
						echo "\n".str_repeat("\t", $recursionLevel).$match[0]."\t<!-- TXT -->";
						recordMap(dns_get_record($match[0], DNS_ALL), ($recursionLevel +1), $match[0]);
					}*/
				break;
			}
		}
	}
?>