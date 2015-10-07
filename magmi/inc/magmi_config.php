<?php
require_once ("properties.php");

/**
 * Directory based configuration object
 * Uses a property file
 * 
 * @author dweeves
 *        
 */
class DirbasedConfig extends Properties
{
    protected $_basedir = null;
    protected $_confname = null;

    public function __construct($basedir, $confname)
    {
        $this->_basedir = $basedir;
        $this->_confname = $basedir . DIRSEP . $confname;
    }

    public function get($secname, $pname, $default = null)
    {
        if (!isset($this->_props))
        {
            $this->load();
        }
        return parent::get($secname, $pname, $default);
    }

    public function getConfFile()
    {
        return $this->_confname;
    }

    public function getLastSaved($fmt)
    {
        if(file_exists($this->_confname))
        {
            $lastsaved=strftime($fmt, filemtime($this->_confname));
        }
        else
        {
            $lastsaved="never";
        }
        return $lastsaved;
    }

    public function load($name = null)
    {
       if(!isset($this->_props))
       {
        if ($name == null)
        {
            $name = $this->_confname;
        }
        
        if (!file_exists($name))
        {
            $this->save();
        }
        parent::load($name);
       }
    }

    public function save($arr = null)
    {
        if ($arr != null)
        {
            $this->setPropsFromFlatArray($arr);
        }
        return parent::save($this->_confname);
    }

    public function saveTo($arr, $newdir)
    {
        if (!file_exists($newdir))
        {
            mkdir($newdir, Magmi_Config::getInstance()->getDirMask());
        }
        $val = parent::save($newdir . DIRSEP . basename($this->_confname));
        $this->_basedir = $newdir;
        $this->_confname = $newdir . DIRSEP . basename($this->_confname);
        return $val;
    }

    public function getConfDir()
    {
        return $this->_basedir;
    }
}

class ProfileBasedConfig extends DirbasedConfig
{
    private static $_script = __FILE__;
    protected $_profile = null;

    public function getProfileDir()
    {
        $subdir = ($this->_profile == "default" ? "" : DIRSEP . $this->_profile);
        $confdir = Magmi_Config::getInstance()->getConfDir()."$subdir";
        if (!file_exists($confdir))
        {
            @mkdir($confdir, Magmi_Config::getInstance()->getDirMask());
        }
        return realpath($confdir);
    }

    public function __construct($fname, $profile = null)
    {
        $this->_profile = $profile;
        parent::__construct($this->getProfileDir(), $fname);
    }

    public function getProfile()
    {
        return $this->_profile;
    }
}

class Magmi_Config extends DirbasedConfig
{
    private static $_instance = null;
    private $_defaultconfigname = null;
    public static $conffile = null;

    public function getConfDir()
    {
        if($this->_confname==null)
        {
            $confdir = realpath(dirname(dirname(__FILE__)) . DIRSEP . "conf");
            return $confdir;
        }
        else
        {
            return $this->_basedir;
        }
    }

    public function __construct()
    {
        parent::__construct($this->getConfDir(), "magmi.ini");
    }

    public function getDirMask()
    {
        return octdec($this->get("GLOBAL", "dirmask", "755"));
    }

    public function getFileMask()
    {
        return octdec($this->get("GLOBAL", "filemask", "644"));
    }

    public function getMagentoDir()
    {
        $bd = $this->get("MAGENTO", "basedir");
        $dp = $bd[0] == "." ? dirname(__FILE__) . "/" . $bd : $bd;
        return realpath($dp);
    }

    public static function getInstance()
    {
        if (self::$_instance == null)
        {
            self::$_instance = new Magmi_Config();
        }
        return self::$_instance;
    }

    public function isDefault()
    {
        return !file_exists($this->_confname);
    }

    public function load($name = null)
    {
        if($name==null)
        {
         $conf = (!$this->isDefault()) ? $this->_confname : $this->_confname . ".default";
        }
        else 
        {
            $conf=$name;
        }
        parent::load($conf);
        $this->_confname = basename($conf);
        if($this->_confname!=$conf)
        {
             $this->_basedir=dirname($conf);
        }
        if ($this->hasSection('USE_ALTERNATE'))
        {
            $alternate=$this->get("USE_ALTERNATE", "file");
            $this->_confname = basename($alternate);
            $this->_basedir=dirname($alternate);
            $alt = true;
            $this->set("USE_ALTERNATE", "file", $this->_confname);
            parent::load($this->_confname);
        }
        
      
        return $this;
    }

    public function save($arr = null)
    {
        if (isset($arr["USE_ALTERNATE:file"]))
        {
            $this->_confname = $arr["USE_ALTERNATE:file"];
            unset($arr["USE_ALTERNATE:file"]);
        }
        if ($arr !== null)
        {
            foreach ($arr as $k => $v)
            {
                if (!preg_match("/\w+:\w+/", $k))
                {
                    unset($arr[$k]);
                }
            }
        }
        return parent::save($arr);
    }

    public function getProfileList()
    {
        $proflist = array();
        $candidates = scandir($this->getConfDir());
        foreach ($candidates as $candidate)
        {
            if (is_dir($this->getConfDir() . DIRSEP . $candidate) && $candidate[0] != "." &&
                 substr($candidate, 0, 2) != "__")
            {
                $proflist[] = $candidate;
            }
        }
        return $proflist;
    }
}

class EnabledPlugins_Config extends ProfileBasedConfig
{

    public function __construct($profile = "default")
    {
        parent::__construct("plugins.conf", $profile);
    }

    public function getEnabledPluginFamilies($typelist)
    {
        $btlist = array();
        if (!is_array($typelist))
        {
            $typelist = explode(",", $typelist);
        }
        foreach ($typelist as $pfamily)
        {
            $btlist[$pfamily] = $this->getEnabledPluginClasses($pfamily);
        }
        return $btlist;
    }

    public function getEnabledPluginClasses($type)
    {
        $type = strtoupper($type);
        $cslist = $this->get("PLUGINS_$type", "classes");
        if ($cslist == null)
        {
            $cslist = $this->get("PLUGINS_$type", "class");
            $epc = ($cslist == null ? array() : array($cslist));
        }
        else
        {
            $epc = ($cslist == "" ? array() : explode(",", $cslist));
        }
        return $epc;
    }

    public function isPluginEnabled($type, $pclass)
    {
        return in_array($pclass, $this->getEnabledPluginClasses($type));
    }
}
