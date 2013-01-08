<?php

require_once(APPPATH.'libraries/REST_Controller.php'); 
require_once(APPPATH.'third_party/Spyc.php'); 

class Modpack extends REST_Controller
{
	
	public function index_get()
	{
		$yaml = file_get_contents('http://mirror.technicpack.net/Technic/modpacks.yml');
		$modpacks = Spyc::YAMLLoad($yaml);
		$this->response($modpacks);
	}

	public function details_get()
	{
		if ($this->uri->segment(3) != NULL) {
			$request = $this->uri->segment(3);
		} else { $request = NULL; }

		$yaml = file_get_contents('http://mirror.technicpack.net/Technic/'.$this->uri->segment(2).'/modpack.yml');
		$modpackData = Spyc::YAMLLoad($yaml);
				
		switch ($request) 
		{
			case "MD5":
				$md5 = array("md5" => md5($yaml));
				$this->response($md5);
				break;
			case "build":
				if ($this->uri->segment(4) == NULL OR !array_key_exists($this->uri->segment(4),$modpackData['builds'])) {
					$this->response(array('error' => 'Build not found'),404);
				} else {
					$buildRequest = $this->uri->segment(4);
					$versionData = $modpackData['builds'][$buildRequest];
					$this->response($versionData);
				}
				break;
			default:
				// Generate build list
				$modpackBuilds = array();
				foreach ($modpackData['builds'] as $build => $value)
				{
					array_push($modpackBuilds,$build);
				}

				$modpack = array(
								"recommended" => $modpackData['recommended'],
								"latest" => $modpackData['latest'],
								"builds" => $modpackBuilds
								);
				$this->response($modpack);
		}
	}

}

?>