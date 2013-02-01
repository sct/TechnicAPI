<?php

require_once(APPPATH.'libraries/REST_Controller.php'); 
require_once(APPPATH.'third_party/Spyc.php'); 

class Cache extends REST_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->rest_format = "json";
	}

	public function index_get()
	{
		$yaml = file_get_contents(FCPATH.'checksum.yml');
		$checksum = Spyc::YAMLLoad($yaml);

		// DEBUG
		echo "<pre>";
		print_r($checksum);
	}

	public function update_get()
	{
		$checksum = file_get_contents($this->config->item('repo_url').'CHECKSUM.md5');
		$md5Array = explode("\n",$checksum);
		$finalized = array();
		foreach ($md5Array as $entry)
		{
			$split = explode('|',$entry);
			$md5 = $split[0];
			if (isset($split[1]))
			{
			$directory = explode("\\",$split[1]);
				if ($directory[0] == "mods")
				{
					if (!array_key_exists($directory[1],$finalized)) {
						$finalized[$directory[1]] = array();
					}

					// Check if basemods and change up regex
					if ($directory[1] == "basemods") {
						$regex = "/-([\w\d.-]+).zip/";
						preg_match($regex,$directory[2],$versionSplit);
						$finalized[$directory[1]][$versionSplit[1]] = $md5;
					} else {
						$versionSplit = explode("-",$directory[2]);
						$splitCount = count($versionSplit) - 1;
						$modversion = str_replace("\r", "", $versionSplit[$splitCount]);

						$modversion = str_replace(".zip", "", $modversion);
						$finalized[$directory[1]][$modversion] = $md5;
					}
				}
			}
		}
		$yaml = Spyc::YAMLDump($finalized);

		$ymlFileName = FCPATH."checksum.yml";
		$fstream = fopen($ymlFileName,'w');
		fwrite($fstream,$yaml);
		fclose($fstream);
	}

	public function mod_get()
	{
		$yaml = file_get_contents(FCPATH.'checksum.yml');
		$checksum = Spyc::YAMLLoad($yaml);

		$success = 0;

		if ($this->uri->segment(3) != NULL)
		{
			$modName = $this->uri->segment(3);
			if (array_key_exists($modName,$checksum))
			{
				if ($this->uri->segment(4) != NULL)
				{
					$modVersion = $this->uri->segment(4);
					
					if (array_key_exists($modVersion,$checksum[$modName]))
					{
						if ($this->uri->segment(5) != NULL && $this->uri->segment(5) == "MD5")
						{
							// HOLY GOD NESTED IFS ARE TERRIBLE KILL ME NOW
							$this->response(array("MD5" => $checksum[$modName][$modVersion]));
							$success = 1;
							// hooray
						} else {
							$this->response(array($modName => $this->config->item('repo_url')."mods/".$modName."/".$modName."-".$modVersion.".zip"));
							$success = 1;
						}
					}
				}
			}
		}

		if ($success == 0)
			$this->response(array("error" => "There was an error in your request"),404);
	}
}

?>