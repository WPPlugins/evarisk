<?php
/**
 * sfZip Library v1.0
 *
 * @author exoia
 */
 
/** Readme v1.0
*	$archive = new Zip("exemple.zip");//Instantiation du zip et de son nom
*	$archive->setFiles(array("path/file.csv","path/file2.png"));//Fichiers à inclure dans le zip
*	$archive->compressToPath("path/to/archive.zip");//Creation du fichier zip
*/
require_once dirname(__FILE__).'/lib/zip.lib.php';


class eva_Zip
{
	protected $zip;
	protected $archive = null;
	protected $filename;
	protected $files = null;
	
  /**
   * Constructs a sfZip object
   *
   * @param string $new_filename - the name of the zip file
   */
	public function __construct( $new_filename = "archive.zip")
	{
		$this->zip = new eva_zipfile();
		
		if (is_string($new_filename))
		{
			$this->filename = $new_filename;
		}else{
			$this->filename = "archive.zip";
		}
	}

  /**
   * Getter for the Zip object
   * @return eva_zipfile $zip
   * @author Eoxia
   */
	public function getZip()
	{
		return $this->zip;
	}

  /**
   * Setter for the Zip object
   * @param eva_zipfile $new_zip
   * @author Eoxia
   */
	protected function setZip( $new_zip)
	{
		$this->zip = $new_zip;
	}
	
  /**
   * Getter for the Archive
   * @return zip $archive
   * @author Eoxia
   */
	protected function getArchive()
	{
		return $this->archive;
	}

  /**
   * Setter for Archive file
   * @param eva_zipfile $new_archive
   * @author Eoxia
   */
	private function setArchive( $new_archive)
	{
		$this->archive = $new_archive;
	}

  /**
   * Getter for download ARchive
   * @return zip $archive to Navigator
   * @author Eoxia
   */	
	public function getArchiveToClient()
	{
		header('Content-Type: application/x-zip');
		header('Content-Disposition: inline; filename='.$this->getFilename());
		
		if(is_null($this->getArchive()))
		{
			echo "Aucune archive disponible.";
		}else{
			echo $this->getArchive();
		}
	}
	
  /**
   * Getter for Name of the zip
   * @return string $filename - Name of the compressed file
   * @author Eoxia
   */	
	public function getFilename()
	{
		return $this->filename;
	}

  /**
   * Setter for Name of the zip
   * @param string $new_filename - Name of the compressed file
   * @author Eoxia
   */
	public function setFilename( $new_filename)
	{
		if (is_string($new_filename))
		{
			$this->filename = $new_filename;
		}else{
			$this->filename = "archive.zip";
		}
	}
	
  /**
   * Getter for Files to compress
   * @return array $files - Array of files to compress
   * @author Eoxia
   */	
	public function getFiles()
	{
		return $this->files;
	}

  /**
   * Setter for Files to compress
   * @param array $new_files - Array of files to compress
   * @author Eoxia
   */
	public function setFiles( $new_files)
	{
		if (is_array($new_files))
		{
			$this->files = $new_files;
		}else{
			$this->files = null;
		}
	}
	
  /**
   * Counter for Files to compress
   * @return int $count - Number of files to compress
   * @author Eoxia
   */	
	public function getCountFiles()
	{
		if(is_null($this->getFiles()))
		{
			return 0;
		}else{
			return count($this->getFiles());
		}
	}
	
  /**
   * Method to creat archive file
   * @param string $path - Path to put the zip file
   * @return bool $check - False if error
   * @author Eoxia
   */	
	public function compressToPath( $path)
	{
		if(is_string($path))
		{
			$i = 0;
			$filesToZip = $this->getFiles();
			
			while( $this->getCountFiles() > $i )
			{
				$fo = fopen($filesToZip[$i],'r');
				$contenu = fread($fo, filesize($filesToZip[$i]));
				fclose($fo);
				$this->getZip()->addfile($contenu, basename($filesToZip[$i]));
				$i++;
			}
			$this->setArchive($this->getZip()->file());
			
			$open = fopen( $path.$this->getFilename(), "wb");
			if(fwrite($open, $this->getArchive()))
			{
				fclose($open);
				return true;
			}else{
				fclose($open);
				return false;
			}
		}else{
			return false;
		}
	}
}