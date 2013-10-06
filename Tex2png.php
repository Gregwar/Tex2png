<?php

namespace Gregwar\Tex2png;

use Gregwar\Cache\Cache;

/**
 * Helper to generate PNG from LaTeX formula
 *
 * @author Grégoire Passault <g.passault@gmail.com>
 */
class Tex2png
{
    
    /**
    * Where is the LaTex ?
    */
    const LATEX = "/usr/bin/latex";
    
    /**
    * Where is the DVIPNG ?
    */
    const DVIPNG = "/usr/bin/dvipng";

    /**
     * LaTeX packges
     */
    public $packages = array('amssymb,amsmath', 'color', 'amsfonts', 'amssymb', 'pst-plot');
    
    /**
     * Cache directory
     */
    protected $cacheDir = 'cache/tex';

    /**
     * Actual cache directory
     */
    protected $actualCacheDir = null;

    /**
     * Temporary directory
     * This is needed to write temporary files needed for
     * generation
     */
    protected $tmpDir = '/tmp';

    /**
     * Target file
     */
    protected $file = null;

    /**
     * Cache
     */
    protected $cache = null;

    /**
     * Target actual file
     */
    protected $actualFile = null;

    /**
     * Hash
     */
    protected $hash;

    /**
     * LaTeX formula
     */
    protected $formula;

    /**
     * Target density
     */
    protected $density;

    /**
     * Error (if any)
     */
    protected $error = null;

    public static function create($formula, $density = 155)
    {
        return new self($formula, $density);
    }

    public function __construct($formula, $density = 155)
    {
        $datas = array(
            'formula' => $formula,
            'density' => $density,
        );

        $this->formula = $formula;
        $this->density = $density;
        $this->hash = sha1(serialize($datas));
        $this->cache = new Cache;

        return $this;
    }

    /**
     * Sets the target directory
     */
    public function saveTo($file)
    {
        $this->actualFile = $this->file = $file;

        return $this;
    }

    /**
     * Generates the image
     */
    public function generate()
    {
        $tex2png = $this;

        $generate = function($target) use ($tex2png) {
            $tex2png->actualFile = $target;

            try {
                // Generates the LaTeX file
                $this->createFile();
           
                // Compile the latexFile     
                $this->latexFile();

                // Converts the DVI file to PNG
                $this->dvi2png();
            } catch (\Exception $e) {
                $this->error = $e;
            }

            $this->clean();
        };

        if ($this->actualFile === null) {
            $target = $this->hash . '.png';
            $this->cache->getOrCreate($target, array(), $generate);

            $this->file = $this->cache->getCacheFile($target);
            $this->actualFile = $this->cache->getCacheFile($target, true);
        } else {
            $generate($this->actualFile);
        }

        return $this;
    }

    /**
     * Create the LaTeX file
     */
    protected function createFile()
    {
        $tmpfile = $this->tmpDir . '/' . $this->hash . '.tex';

        $tex = '\documentclass[12pt]{article}'."\n";
        
        $tex .= '\usepackage[utf8]{inputenc}'."\n";

        // Packages
        foreach ($this->packages as $package) {
            $tex .= '\usepackage{' . $package . "}\n";
        }
        
        $tex .= '\begin{document}'."\n";
        $tex .= '\pagestyle{empty}'."\n";
        $tex .= '\begin{displaymath}'."\n";
        
        $tex .= $this->formula."\n";
        
        $tex .= '\end{displaymath}'."\n";
        $tex .= '\end{document}'."\n";

        if (file_put_contents($tmpfile, $tex) === false) {
            throw new \Exception('Failed to open target file');
        }
    }

    /**
     * Compiles the LaTeX to DVI
     */
    protected function latexFile()
    {
        $command = 'cd ' . $this->tmpDir . '; ' . self::LATEX . ' ' . $this->hash . '.tex < /dev/null |grep ^!|grep -v Emergency > ' . $this->tmpDir . '/' .$this->hash . '.err 2> /dev/null 2>&1';

        shell_exec($command);

        if (!file_exists($this->tmpDir . '/' . $this->hash . '.dvi')) {
            throw new \Exception('Unable to compile LaTeX formula (is latex installed? check syntax)');
        }
    }

    /**
     * Converts the DVI file to PNG
     */
    protected function dvi2png()
    {
        // XXX background: -bg 'rgb 0.5 0.5 0.5'
        $command = self::DVIPNG . ' -q -T tight -D ' . $this->density . ' -o ' . $this->actualFile . ' ' . $this->tmpDir . '/' . $this->hash . '.dvi 2>&1';

        if (shell_exec($command) === null) {
            throw new \Exception('Unable to convert the DVI file to PNG (is dvipng installed?)');
        }
    }

    /**
     * Cleaning
     */
    protected function clean()
    {
        @shell_exec('rm -f ' . $this->tmpDir . '/' . $this->hash . '.* 2>&1');
    }

    /**
     * Gets the HTML code for the image
     */
    public function html()
     {
        if ($this->error)
        {
            return '<span style="color:red">LaTeX: syntax error (' . $this->error->getMessage() . ')</span>';
        }
        else
        {
            return '<img class="formula" title="Formula" src="' . $this->getFile() . '">';
        }
    }

    /**
     * Sets the cache directory
     */
    public function setCacheDirectory($directory)
    {
        $this->cache->setCacheDirectory($directory);
    }

    /**
     * Sets the actual cache directory
     */
    public function setActualCacheDirectory($actualDirectory)
    {
        $this->cache->setActualCacheDirectory($actualDirectory);
    }

    /**
     * Sets the temporary directory
     */
    public function setTempDirectory($directory)
    {
        $this->tmpDir = $directory;
    }

    /**
     * Returns the PNG file
     */
    public function getFile()
    {
        return $this->hookFile($this->file);
    }

    /**
     * Hook that helps extending this class (eg: adding a prefix or suffix)
     */
    public function hookFile($filename)
    {
        return $filename;
    }

    /**
     * The string representation is the cache file
     */
    public function __toString()
    {
        return $this->getFile();
    }
}
