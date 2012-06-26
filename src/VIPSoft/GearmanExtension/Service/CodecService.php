<?php
/**
 * @copyright 2012 Anthon Pang
 * @license MIT
 */

namespace VIPSoft\GearmanExtension\Service;

/**
 * Coder-decoder service
 *
 * @todo encryption/decryption
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class CodecService
{
    /**
     * Enable compression?
     *
     * @var boolean
     */
    private $enableCompression = false;

    /**
     * Constructor
     *
     * @param boolean $enableCompression True to enable compression; default is no compression
     */
    public function __construct($enableCompression = false)
    {
        $this->enableCompression = $enableCompression && function_exists('gzcompress') && function_exists('gzuncompress');
    }

    /**
     * Encode
     *
     * @param mixed $data
     *
     * @return string
     */
    public function encode($data)
    {
        $data = serialize($data);

        if ($this->enableCompression) {
            $data = gzcompress($data);
        }

        return $data;
    }

    /**
     * Decode
     *
     * @param string $data
     *
     * @return mixed
     */
    public function decode($data)
    {
        if ($this->enableCompression) {
            $data = gzuncompress($data);
        }

        $data = unserialize($data);

        return $data;
    }
}
