<?php

class PreReleasePageTest extends SapphireTest{
	static $fixture_file = "addons/tests/PreReleasePageTest.yml";
	
	//2.2 latest stable release exists and is the most recent. so nothing show for this release line
	function test2point2(){
		$page = $this->objFromFixture('PreReleasesPage', 'page');
		$s = $page->latestStable("2.2");
		$p = $page->latestPrerelease("2.2");
		
		$this->assertTrue(isset($s));
		$this->assertTrue(isset($p));
		
		$latest = $page->compareToGetLatest($s, $p);
		$this->assertFalse(isset($latest));
	}
	
	//2.3 latest stable is older than latest pre-release which is faked
	function test2point3(){
		$page = $this->objFromFixture('PreReleasesPage', 'page');
		$s = $page->latestStable("2.3");
		$p = $page->latestPrerelease("2.3");
		
		$this->assertTrue(isset($s));
		$this->assertTrue(isset($p));
		
		$latest = $page->compareToGetLatest($s, $p);
		$this->assertTrue(isset($latest));
		$this->assertEquals($p, $latest);
		$this->assertContains('2.3.5-rc2', $latest['url']);
	}
	
	
	//2.4 there is no stable, this latest pre-release is alpha1.
	function test2point4(){
		$page = $this->objFromFixture('PreReleasesPage', 'page');
		$s = $page->latestStable("2.4");
		$p = $page->latestPrerelease("2.4");
		
		$this->assertFalse(isset($s));
		$this->assertTrue(isset($p));
		$this->assertContains('2.4.0-alpha1', $p['url']);
	}
	
	//2.5 there is no stable, this latest pre-release is 2.5.1-alpha1, though 2.5.0-alpha1, 2.5.0-beta1 and 2.5.0-beta2 all exsit.
	function test2point5(){
		$page = $this->objFromFixture('PreReleasesPage', 'page');
		$s = $page->latestStable("2.5");
		$p = $page->latestPrerelease("2.5");
		
		$this->assertFalse(isset($s));
		$this->assertTrue(isset($p));
		$this->assertContains('2.5.1-alpha1', $p['url']);
	}
	
	//2.6 there is no stable, this latest pre-release is 2.6.0-rc1, though 2.6.0-alpha1, 2.6.0-alpha2, 2.6.0-alpha3,  2.6.0-beta1, 2.6.0-beta2 all exsit.
	function test2point6(){
		$page = $this->objFromFixture('PreReleasesPage', 'page');
		$s = $page->latestStable("2.6");
		$p = $page->latestPrerelease("2.6");
		
		$this->assertFalse(isset($s));
		$this->assertTrue(isset($p));
		$this->assertContains('2.6.0-rc1', $p['url']);
	}
	
	//given major releases as 2.2, 2.3, 2.4, 2.5 and 2.6, then there should be 4 lastest pre-releases rendered into pre-releases page.
	function testNumbersOfPreReleases(){
		$page = $this->objFromFixture('PreReleasesPage', 'page');
		$this->assertEquals($page->Downloads()->count(), 4);
	} 
	
	
	
	/**
	 * what is setup here is add serialised array to each of the ChildDirsPacked field of SvnInfoCache object as follow:
	 */
	/*
	SvnInfoCache:
	    software_svn_installer:
	        URL: http://svn.testonly.com/open/phpinstaller/tags
	        ChildDirsPacked: a:12:{
	            s:5:"2.2.0";a:2:{s:4:"date";s:27:"2007-11-28T04:02:36.634790Z";s:3:"rev";s:5:"45871";}
	            s:5:"2.2.1";a:2:{s:4:"date";s:27:"2007-12-20T20:49:46.413534Z";s:3:"rev";s:5:"47425";}
	            s:5:"2.2.2";a:2:{s:4:"date";s:27:"2008-07-23T04:38:31.374385Z";s:3:"rev";s:5:"58892";}
	            s:5:"2.2.4";a:2:{s:4:"date";s:27:"2009-03-20T01:32:48.299903Z";s:3:"rev";s:5:"73434";}
	            s:5:"2.3.0";a:2:{s:4:"date";s:27:"2009-02-23T01:55:40.788117Z";s:3:"rev";s:5:"72082";}
	            s:5:"2.3.1";a:2:{s:4:"date";s:27:"2009-03-20T01:59:01.740102Z";s:3:"rev";s:5:"73444";}
	            s:5:"2.3.2";a:2:{s:4:"date";s:27:"2009-06-19T03:56:43.268686Z";s:3:"rev";s:5:"79637";}
	            s:5:"2.3.3";a:2:{s:4:"date";s:27:"2009-08-03T04:16:46.415335Z";s:3:"rev";s:5:"83506";}
	            s:5:"2.3.4";a:2:{s:4:"date";s:27:"2009-11-27T01:37:55.662016Z";s:3:"rev";s:5:"93760";}
	            s:5:"alpha";a:2:{s:4:"date";s:27:"2009-11-11T00:07:43.686431Z";s:3:"rev";s:5:"91241";}
	            s:4:"beta";a:2:{s:4:"date";s:27:"2009-05-06T01:12:25.650431Z";s:3:"rev";s:5:"76156";}
	            s:2:"rc";a:2:{s:4:"date";s:27:"2009-11-17T01:40:40.415767Z";s:3:"rev";s:5:"91876";}
	        }
	    software_alpha:
	        URL: http://svn.testonly.com/open/phpinstaller/tags/alpha
	        ChildDirsPacked: a:5:{
	            s:12:"2.4.0-alpha1";a:2:{s:4:"date";s:27:"2009-11-11T00:07:43.686431Z";s:3:"rev";s:5:"91241";}
	            s:12:"2.5.0-alpha1";a:2:{s:4:"date";s:27:"2009-12-11T00:07:43.686431Z";s:3:"rev";s:5:"99111";}
	            s:12:"2.5.1-alpha1";a:2:{s:4:"date";s:27:"2010-01-04T00:07:43.686431Z";s:3:"rev";s:5:"99311";}
	            s:12:"2.6.0-alpha1";a:2:{s:4:"date";s:27:"2009-12-11T00:07:43.686431Z";s:3:"rev";s:5:"99211";}
	            s:12:"2.6.0-alpha2";a:2:{s:4:"date";s:27:"2009-12-12T00:07:43.686431Z";s:3:"rev";s:5:"99212";}
	            s:12:"2.6.0-alpha3";a:2:{s:4:"date";s:27:"2009-12-13T00:07:43.686431Z";s:3:"rev";s:5:"99213";}
	        }
	    software_beta:
	        URL: http://svn.testonly.com/open/phpinstaller/tags/beta
	        ChildDirsPacked: a:5:{
	            s:11:"2.3.2-beta1";a:2:{s:4:"date";s:27:"2009-05-06T01:12:25.650431Z";s:3:"rev";s:5:"76156";}
	            s:11:"2.5.0-beta1";a:2:{s:4:"date";s:27:"2010-01-01T01:12:25.650431Z";s:3:"rev";s:5:"99113";}
	            s:11:"2.5.0-beta2";a:2:{s:4:"date";s:27:"2010-01-02T01:12:25.650431Z";s:3:"rev";s:5:"99114";}
	            s:11:"2.6.0-beta1";a:2:{s:4:"date";s:27:"2010-01-03T01:12:25.650431Z";s:3:"rev";s:5:"99214";}
	            s:11:"2.6.0-beta2";a:2:{s:4:"date";s:27:"2010-01-04T01:12:25.650431Z";s:3:"rev";s:5:"99215";}
	        }
	    software_rc:
	        URL: http://svn.testonly.com/open/phpinstaller/tags/rc
	        ChildDirsPacked: a:31:{
	            s:9:"2.2.0-rc1";a:2:{s:4:"date";s:27:"2007-11-13T02:23:21.709636Z";s:3:"rev";s:5:"44713";}
	            s:9:"2.2.0-rc2";a:2:{s:4:"date";s:27:"2007-11-20T04:36:11.096359Z";s:3:"rev";s:5:"45221";}
	            s:9:"2.2.0-rc3";a:2:{s:4:"date";s:27:"2007-11-23T04:03:14.818156Z";s:3:"rev";s:5:"45517";}
	            s:9:"2.2.0-rc4";a:2:{s:4:"date";s:27:"2007-11-27T04:36:07.410679Z";s:3:"rev";s:5:"45736";}
	            s:9:"2.2.1-rc1";a:2:{s:4:"date";s:27:"2007-12-14T02:47:56.104163Z";s:3:"rev";s:5:"46882";}
	            s:9:"2.2.1-rc2";a:2:{s:4:"date";s:27:"2007-12-18T03:32:24.637370Z";s:3:"rev";s:5:"47253";}
	            s:5:"2.2.2";a:2:{s:4:"date";s:27:"2008-07-23T04:38:32.217242Z";s:3:"rev";s:5:"58898";}
	            s:9:"2.2.2-rc1";a:2:{s:4:"date";s:27:"2008-07-23T04:38:32.059694Z";s:3:"rev";s:5:"58897";}
	            s:9:"2.2.2-rc2";a:2:{s:4:"date";s:27:"2008-07-23T04:38:31.963337Z";s:3:"rev";s:5:"58896";}
	            s:9:"2.2.2-rc3";a:2:{s:4:"date";s:27:"2008-07-23T04:38:31.881395Z";s:3:"rev";s:5:"58895";}
	            s:9:"2.2.2-rc4";a:2:{s:4:"date";s:27:"2008-07-23T04:38:31.759325Z";s:3:"rev";s:5:"58894";}
	            s:9:"2.2.2-rc5";a:2:{s:4:"date";s:27:"2008-07-23T04:38:31.656348Z";s:3:"rev";s:5:"58893";}
	            s:5:"2.2.3";a:2:{s:4:"date";s:27:"2008-10-31T04:10:55.223930Z";s:3:"rev";s:5:"65009";}
	            s:9:"2.2.3-rc1";a:2:{s:4:"date";s:27:"2008-10-31T03:15:08.500720Z";s:3:"rev";s:5:"64999";}
	            s:9:"2.2.4-rc1";a:2:{s:4:"date";s:27:"2009-03-20T00:30:06.623406Z";s:3:"rev";s:5:"73422";}
	            s:9:"2.3.0-rc1";a:2:{s:4:"date";s:27:"2008-11-13T04:30:11.230616Z";s:3:"rev";s:5:"65816";}
	            s:9:"2.3.0-rc2";a:2:{s:4:"date";s:27:"2008-11-28T05:36:51.420720Z";s:3:"rev";s:5:"66948";}
	            s:9:"2.3.0-rc3";a:2:{s:4:"date";s:27:"2009-01-30T05:09:15.493788Z";s:3:"rev";s:5:"71042";}
	            s:9:"2.3.0-rc4";a:2:{s:4:"date";s:27:"2009-02-17T03:19:41.073281Z";s:3:"rev";s:5:"71934";}
	            s:9:"2.3.1-rc1";a:2:{s:4:"date";s:27:"2009-03-12T00:51:36.509262Z";s:3:"rev";s:5:"72946";}
	            s:9:"2.3.1-rc2";a:2:{s:4:"date";s:27:"2009-03-16T06:33:52.302956Z";s:3:"rev";s:5:"73122";}
	            s:9:"2.3.2-rc1";a:2:{s:4:"date";s:27:"2009-05-20T05:04:03.207187Z";s:3:"rev";s:5:"77353";}
	            s:9:"2.3.2-rc2";a:2:{s:4:"date";s:27:"2009-05-28T05:22:16.697641Z";s:3:"rev";s:5:"78089";}
	            s:9:"2.3.2-rc3";a:2:{s:4:"date";s:27:"2009-06-15T06:09:58.248513Z";s:3:"rev";s:5:"79231";}
	            s:9:"2.3.2-rc4";a:2:{s:4:"date";s:27:"2009-06-16T04:41:58.349407Z";s:3:"rev";s:5:"79340";}
	            s:9:"2.3.3-rc1";a:2:{s:4:"date";s:27:"2009-07-21T02:30:38.536390Z";s:3:"rev";s:5:"82219";}
	            s:9:"2.3.3-rc2";a:2:{s:4:"date";s:27:"2009-07-29T02:18:06.451325Z";s:3:"rev";s:5:"83066";}
	            s:9:"2.3.4-rc1";a:2:{s:4:"date";s:27:"2009-11-17T01:40:40.415767Z";s:3:"rev";s:5:"91876";}
	            s:9:"2.3.5-rc1";a:2:{s:4:"date";s:27:"2009-12-17T01:40:40.415767Z";s:3:"rev";s:5:"99100";}
	            s:9:"2.3.5-rc2";a:2:{s:4:"date";s:27:"2009-12-18T01:40:40.415767Z";s:3:"rev";s:5:"99101";}
	            s:9:"2.6.0-rc1";a:2:{s:4:"date";s:27:"2010-01-05T01:40:40.415767Z";s:3:"rev";s:5:"99216";}         
	        }
	*/	
	function setUp() {
		parent::setUp();
		$software_svn_installer = $this->objFromFixture('SvnInfoCache', 'software_svn_installer');
		$software_svn_installer->ChildDirsPacked = <<<STR
a:12:{s:5:"2.2.0";a:2:{s:4:"date";s:27:"2007-11-28T04:02:36.634790Z";s:3:"rev";s:5:"45871";}s:5:"2.2.1";a:2:{s:4:"date";s:27:"2007-12-20T20:49:46.413534Z";s:3:"rev";s:5:"47425";}s:5:"2.2.2";a:2:{s:4:"date";s:27:"2008-07-23T04:38:31.374385Z";s:3:"rev";s:5:"58892";}s:5:"2.2.4";a:2:{s:4:"date";s:27:"2009-03-20T01:32:48.299903Z";s:3:"rev";s:5:"73434";}s:5:"2.3.0";a:2:{s:4:"date";s:27:"2009-02-23T01:55:40.788117Z";s:3:"rev";s:5:"72082";}s:5:"2.3.1";a:2:{s:4:"date";s:27:"2009-03-20T01:59:01.740102Z";s:3:"rev";s:5:"73444";}s:5:"2.3.2";a:2:{s:4:"date";s:27:"2009-06-19T03:56:43.268686Z";s:3:"rev";s:5:"79637";}s:5:"2.3.3";a:2:{s:4:"date";s:27:"2009-08-03T04:16:46.415335Z";s:3:"rev";s:5:"83506";}s:5:"2.3.4";a:2:{s:4:"date";s:27:"2009-11-27T01:37:55.662016Z";s:3:"rev";s:5:"93760";}s:5:"alpha";a:2:{s:4:"date";s:27:"2009-11-11T00:07:43.686431Z";s:3:"rev";s:5:"91241";}s:4:"beta";a:2:{s:4:"date";s:27:"2009-05-06T01:12:25.650431Z";s:3:"rev";s:5:"76156";}s:2:"rc";a:2:{s:4:"date";s:27:"2009-11-17T01:40:40.415767Z";s:3:"rev";s:5:"91876";}}
STR;
		$software_svn_installer->write();
		
		$software_alpha = $this->objFromFixture('SvnInfoCache', 'software_alpha');
		$software_alpha->ChildDirsPacked = <<<STR
a:6:{s:12:"2.4.0-alpha1";a:2:{s:4:"date";s:27:"2009-11-11T00:07:43.686431Z";s:3:"rev";s:5:"91241";}s:12:"2.5.0-alpha1";a:2:{s:4:"date";s:27:"2009-12-11T00:07:43.686431Z";s:3:"rev";s:5:"99111";}s:12:"2.5.1-alpha1";a:2:{s:4:"date";s:27:"2010-01-04T00:07:43.686431Z";s:3:"rev";s:5:"99311";}s:12:"2.6.0-alpha1";a:2:{s:4:"date";s:27:"2009-12-11T00:07:43.686431Z";s:3:"rev";s:5:"99211";}s:12:"2.6.0-alpha2";a:2:{s:4:"date";s:27:"2009-12-12T00:07:43.686431Z";s:3:"rev";s:5:"99212";}s:12:"2.6.0-alpha3";a:2:{s:4:"date";s:27:"2009-12-13T00:07:43.686431Z";s:3:"rev";s:5:"99213";}}
STR;
		$software_alpha->write();
		
		$software_beta = $this->objFromFixture('SvnInfoCache', 'software_beta');
		$software_beta->ChildDirsPacked = <<<STR
a:5:{s:11:"2.3.2-beta1";a:2:{s:4:"date";s:27:"2009-05-06T01:12:25.650431Z";s:3:"rev";s:5:"76156";}s:11:"2.5.0-beta1";a:2:{s:4:"date";s:27:"2010-01-01T01:12:25.650431Z";s:3:"rev";s:5:"99113";}s:11:"2.5.0-beta2";a:2:{s:4:"date";s:27:"2010-01-02T01:12:25.650431Z";s:3:"rev";s:5:"99114";}s:11:"2.6.0-beta1";a:2:{s:4:"date";s:27:"2010-01-03T01:12:25.650431Z";s:3:"rev";s:5:"99214";}s:11:"2.6.0-beta2";a:2:{s:4:"date";s:27:"2010-01-04T01:12:25.650431Z";s:3:"rev";s:5:"99215";}}
STR;
		$software_beta->write();
		
		$software_rc = $this->objFromFixture('SvnInfoCache', 'software_rc');
		$software_rc->ChildDirsPacked = <<<STR
a:31:{s:9:"2.2.0-rc1";a:2:{s:4:"date";s:27:"2007-11-13T02:23:21.709636Z";s:3:"rev";s:5:"44713";}s:9:"2.2.0-rc2";a:2:{s:4:"date";s:27:"2007-11-20T04:36:11.096359Z";s:3:"rev";s:5:"45221";}s:9:"2.2.0-rc3";a:2:{s:4:"date";s:27:"2007-11-23T04:03:14.818156Z";s:3:"rev";s:5:"45517";}s:9:"2.2.0-rc4";a:2:{s:4:"date";s:27:"2007-11-27T04:36:07.410679Z";s:3:"rev";s:5:"45736";}s:9:"2.2.1-rc1";a:2:{s:4:"date";s:27:"2007-12-14T02:47:56.104163Z";s:3:"rev";s:5:"46882";}s:9:"2.2.1-rc2";a:2:{s:4:"date";s:27:"2007-12-18T03:32:24.637370Z";s:3:"rev";s:5:"47253";}s:5:"2.2.2";a:2:{s:4:"date";s:27:"2008-07-23T04:38:32.217242Z";s:3:"rev";s:5:"58898";}s:9:"2.2.2-rc1";a:2:{s:4:"date";s:27:"2008-07-23T04:38:32.059694Z";s:3:"rev";s:5:"58897";}s:9:"2.2.2-rc2";a:2:{s:4:"date";s:27:"2008-07-23T04:38:31.963337Z";s:3:"rev";s:5:"58896";}s:9:"2.2.2-rc3";a:2:{s:4:"date";s:27:"2008-07-23T04:38:31.881395Z";s:3:"rev";s:5:"58895";}s:9:"2.2.2-rc4";a:2:{s:4:"date";s:27:"2008-07-23T04:38:31.759325Z";s:3:"rev";s:5:"58894";}s:9:"2.2.2-rc5";a:2:{s:4:"date";s:27:"2008-07-23T04:38:31.656348Z";s:3:"rev";s:5:"58893";}s:5:"2.2.3";a:2:{s:4:"date";s:27:"2008-10-31T04:10:55.223930Z";s:3:"rev";s:5:"65009";}s:9:"2.2.3-rc1";a:2:{s:4:"date";s:27:"2008-10-31T03:15:08.500720Z";s:3:"rev";s:5:"64999";}s:9:"2.2.4-rc1";a:2:{s:4:"date";s:27:"2009-03-20T00:30:06.623406Z";s:3:"rev";s:5:"73422";}s:9:"2.3.0-rc1";a:2:{s:4:"date";s:27:"2008-11-13T04:30:11.230616Z";s:3:"rev";s:5:"65816";}s:9:"2.3.0-rc2";a:2:{s:4:"date";s:27:"2008-11-28T05:36:51.420720Z";s:3:"rev";s:5:"66948";}s:9:"2.3.0-rc3";a:2:{s:4:"date";s:27:"2009-01-30T05:09:15.493788Z";s:3:"rev";s:5:"71042";}s:9:"2.3.0-rc4";a:2:{s:4:"date";s:27:"2009-02-17T03:19:41.073281Z";s:3:"rev";s:5:"71934";}s:9:"2.3.1-rc1";a:2:{s:4:"date";s:27:"2009-03-12T00:51:36.509262Z";s:3:"rev";s:5:"72946";}s:9:"2.3.1-rc2";a:2:{s:4:"date";s:27:"2009-03-16T06:33:52.302956Z";s:3:"rev";s:5:"73122";}s:9:"2.3.2-rc1";a:2:{s:4:"date";s:27:"2009-05-20T05:04:03.207187Z";s:3:"rev";s:5:"77353";}s:9:"2.3.2-rc2";a:2:{s:4:"date";s:27:"2009-05-28T05:22:16.697641Z";s:3:"rev";s:5:"78089";}s:9:"2.3.2-rc3";a:2:{s:4:"date";s:27:"2009-06-15T06:09:58.248513Z";s:3:"rev";s:5:"79231";}s:9:"2.3.2-rc4";a:2:{s:4:"date";s:27:"2009-06-16T04:41:58.349407Z";s:3:"rev";s:5:"79340";}s:9:"2.3.3-rc1";a:2:{s:4:"date";s:27:"2009-07-21T02:30:38.536390Z";s:3:"rev";s:5:"82219";}s:9:"2.3.3-rc2";a:2:{s:4:"date";s:27:"2009-07-29T02:18:06.451325Z";s:3:"rev";s:5:"83066";}s:9:"2.3.4-rc1";a:2:{s:4:"date";s:27:"2009-11-17T01:40:40.415767Z";s:3:"rev";s:5:"91876";}s:9:"2.3.5-rc1";a:2:{s:4:"date";s:27:"2009-12-17T01:40:40.415767Z";s:3:"rev";s:5:"99100";}s:9:"2.3.5-rc2";a:2:{s:4:"date";s:27:"2009-12-18T01:40:40.415767Z";s:3:"rev";s:5:"99101";}s:9:"2.6.0-rc1";a:2:{s:4:"date";s:27:"2010-01-05T01:40:40.415767Z";s:3:"rev";s:5:"99216";}}
STR;
		$software_rc->write();
	}
}

?>