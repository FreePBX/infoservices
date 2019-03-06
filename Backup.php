<?php
namespace FreePBX\modules\Infoservices;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
	public function runBackup($id,$transaction){
		$configs = [
			'features' => $this->dumpFeatureCodes()
		];
		$this->addConfigs($configs);
	}
}
