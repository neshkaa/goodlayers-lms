<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace pxlrbt\Cf7Cleverreach\Vendor\Monolog\Handler;

use pxlrbt\Cf7Cleverreach\Vendor\Monolog\Logger;
use pxlrbt\Cf7Cleverreach\Vendor\Monolog\Formatter\NormalizerFormatter;
use pxlrbt\Cf7Cleverreach\Vendor\Doctrine\CouchDB\CouchDBClient;
/**
 * CouchDB handler for Doctrine CouchDB ODM
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class DoctrineCouchDBHandler extends AbstractProcessingHandler
{
    private $client;
    public function __construct(CouchDBClient $client, $level = Logger::DEBUG, $bubble = \true)
    {
        $this->client = $client;
        parent::__construct($level, $bubble);
    }
    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        $this->client->postDocument($record['formatted']);
    }
    protected function getDefaultFormatter()
    {
        return new NormalizerFormatter();
    }
}
