<?php
require_once('testdata/autorun/autorun.php');
require_once('../unlink_directory.php');

class TestOfFileDelete extends UnitTestCase {
	public $unlink_directory;
	public function setUp() {
		$this->unlink_directory = new unlink_directory();
	}
	function testremove_directory( $directory = 'demo' ) {
		$this->unlink_directory->remove_directory( $directory );
		$this->assertFalse(file_exists('demo'));
	}
	public function tearDown() {
		unset( $this->unlink_directory );
	}
}
?>
