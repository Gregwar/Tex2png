<?php

namespace Gregwar\Tex2png;

/**
 * Helper to generate PNG from LaTeX formula
 *
 * @author Grégoire Passault <g.passault@gmail.com>
 */
class Tex2png
{
    /**
     * Cache directory
     */
    protected $cacheDir = 'cache/tex';

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
        return new Tex2png($formula, $density);
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

        return $this;
    }

    /**
     * Sets the target directory
     */
    public function saveTo($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Generates the image
     */
    public function generate()
    {
        if ($this->file === null) 
        {
            $this->file = $this->generateFileFromHash($this->hash) . '.png';
        }

        if (!file_exists($this->file))
        {
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
        }

        return $this;
    }

    /**
     * Create the LaTeX file
     */
    protected function createFile()
    {
        $tmpfile = $this->tmpDir . '/' . $this->hash . '.tex';

        $tex = "\documentclass[fleqn]{article}\n\usepackage{amssymb,amsmath}\n\usepackage{color}\n\usepackage[latin1]{inputenc}\n\\begin{document}\n\\"."thispagestyle{empty}\n\mathindent0cm\n\parindent0cm\n\\begin{displaymath}\n";
        $tex .= $this->formula."\n";
        $tex .= "\end{displaymath}\n\end{document}\n";

        if (file_put_contents($tmpfile, $tex) === false)
        {
            throw new \Exception('Failed to open target file');
        }
    }

    /**
     * Compiles the LaTeX to DVI
     */
    protected function latexFile()
    {
        $command = 'cd ' . $this->tmpDir . ';latex ' . $this->hash . '.tex < /dev/null |grep ^!|grep -v Emergency > ' . $this->tmpDir . '/' .$this->hash . '.err 2> /dev/null';

        shell_exec($command);

        if (!file_exists($this->tmpDir . '/' . $this->hash . '.dvi'))
        {
            throw new \Exception('Unable to compile LaTeX formula (is latex installed? check syntax)');
        }
    }

    /**
     * Converts the DVI file to PNG
     */
    protected function dvi2png()
    {
        // XXX background: -bg 'rgb 0.5 0.5 0.5'
        $command = 'dvipng -q -T tight -D ' . $this->density . ' -o ' . $this->file . ' ' . $this->tmpDir . '/' . $this->hash . '.dvi';

        if (shell_exec($command) === null) 
        {
            throw new \Exception('Unable to convert the DVI file to PNG (is dvipng installed?)');
        }
    }

    /**
     * Cleaning
     */
    protected function clean()
    {
        @shell_exec('rm -f ' . $this->tmpDir . '/' . $this->hash . '.*');
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
            return '<img title="Formula" src="' . $this->file . '">';
        }
    }

    /**
     * Sets the cache directory
     */
    public function setCacheDir($directory)
    {
        $this->cacheDir = $directory;
    }

    /**
     * Sets the temporary directory
     */
    public function setTempDir($directory)
    {
        $this->tmpDir = $directory;
    }

    /**
     * Returns the PNG file
     */
    public function getFile()
    {
        return $this->file;
    }

    /** 
     * Create and returns the absolute directory for a hash
     *
     * @param string $hash the hash
     *
     * @return string the full file name
     */
    public function generateFileFromhash($hash)
    {
        $directory = $this->cacheDir;

        if (!file_exists($directory))
            mkdir($directory); 

        for ($i=0; $i<5; $i++) {
            $c = $hash[$i];
            $directory .= '/'.$c;
            if (!file_exists($directory)) {
                mkdir($directory);
            }   
        }   

        return $directory . '/' . substr($hash,5);
    } 
}

