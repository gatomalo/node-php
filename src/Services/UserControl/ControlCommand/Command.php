<?php

namespace BitWasp\Bitcoin\Node\Services\UserControl\ControlCommand;

use BitWasp\Bitcoin\Node\Chain\BlockIndexInterface;
use BitWasp\Bitcoin\Node\NodeInterface;
use BitWasp\Bitcoin\Transaction\OutPointInterface;
use BitWasp\Bitcoin\Transaction\TransactionInputInterface;
use BitWasp\Bitcoin\Transaction\TransactionInterface;
use BitWasp\Bitcoin\Transaction\TransactionOutputInterface;
use BitWasp\Buffertools\Buffer;

abstract class Command implements CommandInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var array
     */
    protected $params = [];

    public function __construct()
    {
        $this->configure();
    }

    /**
     * @param string $name
     * @return $this
     */
    protected function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $description
     * @return $this
     */
    protected function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $name
     * @param string $description
     * @return $this
     */
    protected function setParam($name, $description)
    {
        $this->params[$name] = $description;
        return $this;
    }

    abstract protected function configure();

    /**
     * @return string
     */
    public function getName()
    {
        if (null === $this->name) {
            throw new \RuntimeException('Name for command not set');
        }

        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        if (null === $this->name) {
            throw new \RuntimeException('Description for command not set');
        }

        return $this->description;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Run the actual command, handling missing parameters & exceptions
     *
     * @param NodeInterface $node
     * @param array $params
     * @return array
     */
    public function run(NodeInterface $node, array $params = [])
    {
        foreach ($this->getParams() as $param => $description) {
            if (!isset($params[$param])) {
                return ['error' => "Missing parameter, `$param`"];
            }
        }

        try {
            $result = $this->execute($node, $params);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage()];
        }

        return $result;
    }

    /**
     * @param BlockIndexInterface $index
     * @return array
     */
    public function convertIndexToArray(BlockIndexInterface $index)
    {
        $header = $index->getHeader();

        return [
            'height' => $index->getHeight(),
            'hash' => $index->getHash()->getHex(),
            'work' => $index->getWork(),
            'workHex' => Buffer::int($index->getWork(), 32)->getHex(),
            'version' => $header->getVersion(),
            'prevBlock' => $header->getPrevBlock()->getHex(),
            'merkleRoot' => $header->getMerkleRoot()->getHex(),
            'nBits' => $header->getBits(),
            'nTimestamp' => $header->getTimestamp(),
            'nNonce' => $header->getNonce()
        ];
    }

    /**
     * @param OutPointInterface $outpoint
     * @return array
     */
    public function convertOutpointToArray(OutPointInterface $outpoint)
    {
        return [
            'txid' => $outpoint->getTxId()->getHex(),
            'vout' => $outpoint->getVout()
        ];
    }

    /**
     * @param TransactionInputInterface $input
     * @return array
     */
    public function convertTxinToArray(TransactionInputInterface $input)
    {
        return [
            'outpoint' => $this->convertOutpointToArray($input->getOutPoint()),
            'scriptSig' => $input->getScript()->getHex(),
            'sequence' => $input->getSequence()
        ];
    }

    /**
     * @param TransactionOutputInterface $output
     * @return array
     */
    public function convertTxoutToArray(TransactionOutputInterface $output)
    {
        return [
            'value' => $output->getValue(),
            'scriptPubKey' => $output->getScript()->getHex()
        ];
    }

    /**
     * @param TransactionInterface $transaction
     * @return array
     */
    public function convertTransactionToArray(TransactionInterface $transaction)
    {
        $inputs = [];
        foreach ($transaction->getInputs() as $input) {
            $inputs[] = $this->convertTxinToArray($input);
        }

        $outputs = [];
        foreach ($transaction->getOutputs() as $output) {
            $outputs[] = $this->convertTxoutToArray($output);
        }

        $buf = $transaction->getBuffer()->getBinary();
        return [
            'hash' => $transaction->getTxId()->getHex(),
            'version' => $transaction->getVersion(),
            'inputs' => $inputs,
            'outputs' => $outputs,
            'locktime' => $transaction->getLockTime(),
            'raw' => bin2hex($buf),
            'size' => strlen($buf)
        ];
    }
}
