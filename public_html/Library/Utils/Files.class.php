<?php
/**
 * Classe para manipulação de arquivos do sistema
 */
namespace Utils;

use XMLWriter;
use Traversable;
use Zend\Stdlib\ArrayUtils;


class Files {

    private $start = null;
    private $ecoder = null;
    private $version = null;
    
    public function __construct(array $config)
    {
        $this->ecoder = isset($config['encoder']) ? $config['encoder']:'UTF-8';
        $this->version = isset($config['version']) ? $config['version']:'1.0';
        $this->start = isset($config['start']) ? $config['start']:'begin';
    }
    
    
    public function processXml($config)
    {
        if ($config instanceof Traversable)
            $config = ArrayUtils::iteratorToArray($config);
        $writer = new XMLWriter($this->ecoder);
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString(str_repeat(' ', 4));

        $writer->startDocument($this->version, $this->ecoder);
        $writer->startElement($this->start);

        foreach ($config as $sectionName => $data) {
            if (!is_array($data)) {
                $writer->writeElement($sectionName, (string) $data);
            } else {
                $this->addBranch($sectionName, $data, $writer);
            }
        }

        $writer->endElement();
        $writer->endDocument();

        return $writer->outputMemory();
    }
    
    
    protected function addBranch($branchName, array $config, XMLWriter $writer)
    {
        $branchType = null;

        foreach ($config as $key => $value) {
            if ($branchType === null) {
                if (is_numeric($key)) {
                    $branchType = 'numeric';
                } else {
                    $writer->startElement($branchName);
                    $branchType = 'string';
                }
            } elseif ($branchType !== (is_numeric($key) ? 'numeric' : 'string')) {
                throw new Exception\RuntimeException('Mixing of string and numeric keys is not allowed');
            }

            if ($branchType === 'numeric') {
                if (is_array($value)) {
                    $this->addBranch($value, $value, $writer);
                } else {
                    $writer->writeElement($branchName, (string) $value);
                }
            } else {
                if (is_array($value)) {
                    $this->addBranch($key, $value, $writer);
                } else {
                    $writer->writeElement($key, (string) $value);
                }
            }
        }

        if ($branchType === 'string') {
            $writer->endElement();
        }
    }
}
?>
