<?php

namespace Leyden\Schoology\API\phpsaml\xmlseclibs;
use DOMXPath;
use DOMElement;
use DOMDocument;
use Exception;
use DOMNode;

/**
 * xmlseclibs.php
 *
 * Copyright (c) 2007, Robert Richards <rrichards@cdatazone.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Robert Richards nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author     Robert Richards <rrichards@cdatazone.org>
 * @copyright  2007 Robert Richards <rrichards@cdatazone.org>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    1.2.2
 */

class XMLSecurityDSig
{
    const XMLDSIGNS = 'http://www.w3.org/2000/09/xmldsig#';
    const SHA1 = 'http://www.w3.org/2000/09/xmldsig#sha1';
    const SHA256 = 'http://www.w3.org/2001/04/xmlenc#sha256';
    const SHA512 = 'http://www.w3.org/2001/04/xmlenc#sha512';
    const RIPEMD160 = 'http://www.w3.org/2001/04/xmlenc#ripemd160';

    const C14N = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
    const C14N_COMMENTS = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments';
    const EXC_C14N = 'http://www.w3.org/2001/10/xml-exc-c14n#';
    const EXC_C14N_COMMENTS = 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments';

    const template = '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
  <ds:SignedInfo>
    <ds:SignatureMethod />
  </ds:SignedInfo>
</ds:Signature>';

    public $sigNode = NULL;
    public $idKeys = array();
    public $idNS = array();
    private $signedInfo = NULL;
    private $xPathCtx = NULL;
    private $canonicalMethod = NULL;
    private $prefix = 'ds';
    private $searchpfx = 'secdsig';

    /* This variable contains an associative array of validated nodes. */
    private $validatedNodes = NULL;

    public function __construct()
    {
        $sigdoc = new DOMDocument();
        $sigdoc->loadXML(XMLSecurityDSig::template);
        $this->sigNode = $sigdoc->documentElement;
    }

    private function getXPathObj()
    {
        if (empty($this->xPathCtx) && !empty($this->sigNode)) {
            $xpath = new DOMXPath($this->sigNode->ownerDocument);
            $xpath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
            $this->xPathCtx = $xpath;
        }
        return $this->xPathCtx;
    }

    static function generate_GUID($prefix = 'pfx')
    {
        $uuid = md5(uniqid(rand(), true));
        $guid = $prefix . substr($uuid, 0, 8) . "-" .
            substr($uuid, 8, 4) . "-" .
            substr($uuid, 12, 4) . "-" .
            substr($uuid, 16, 4) . "-" .
            substr($uuid, 20, 12);
        return $guid;
    }

    public function locateSignature($objDoc)
    {
        if ($objDoc instanceof DOMDocument) {
            $doc = $objDoc;
        }
        else {
            $doc = $objDoc->ownerDocument;
        }
        if ($doc) {
            $xpath = new DOMXPath($doc);
            $xpath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
            $query = ".//secdsig:Signature";
            $nodeset = $xpath->query($query, $objDoc);
            $this->sigNode = $nodeset->item(0);
            return $this->sigNode;
        }
        return NULL;
    }

    public function createNewSignNode($name, $value = NULL)
    {
        $doc = $this->sigNode->ownerDocument;
        if (!is_null($value)) {
            $node = $doc->createElementNS(XMLSecurityDSig::XMLDSIGNS, $this->prefix . ':' . $name, $value);
        }
        else {
            $node = $doc->createElementNS(XMLSecurityDSig::XMLDSIGNS, $this->prefix . ':' . $name);
        }
        return $node;
    }

    public function setCanonicalMethod($method)
    {
        switch ($method) {
            case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
            case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
            case 'http://www.w3.org/2001/10/xml-exc-c14n#':
            case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':
                $this->canonicalMethod = $method;
                break;
            default:
                throw new Exception('Invalid Canonical Method');
        }
        if ($xpath = $this->getXPathObj()) {
            $query = './' . $this->searchpfx . ':SignedInfo';
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($sinfo = $nodeset->item(0)) {
                $query = './' . $this->searchpfx . 'CanonicalizationMethod';
                $nodeset = $xpath->query($query, $sinfo);
                if (!($canonNode = $nodeset->item(0))) {
                    $canonNode = $this->createNewSignNode('CanonicalizationMethod');
                    $sinfo->insertBefore($canonNode, $sinfo->firstChild);
                }
                $canonNode->setAttribute('Algorithm', $this->canonicalMethod);
            }
        }
    }

    private function canonicalizeData($node, $canonicalmethod, $arXPath = NULL, $prefixList = NULL)
    {
        $exclusive = FALSE;
        $withComments = FALSE;
        switch ($canonicalmethod) {
            case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
                $exclusive = FALSE;
                $withComments = FALSE;
                break;
            case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
                $withComments = TRUE;
                break;
            case 'http://www.w3.org/2001/10/xml-exc-c14n#':
                $exclusive = TRUE;
                break;
            case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':
                $exclusive = TRUE;
                $withComments = TRUE;
                break;
        } /* Support PHP versions < 5.2 not containing C14N methods in DOM extension */
        $php_version = explode('.', PHP_VERSION);
        if (($php_version[0] < 5) || ($php_version[0] == 5 && $php_version[1] < 2)) {
            if (!is_null($arXPath)) {
                throw new Exception("PHP 5.2.0 or higher is required to perform XPath Transformations");
            }
            return C14NGeneral($node, $exclusive, $withComments);
        }
        return $node->C14N($exclusive, $withComments, $arXPath, $prefixList);
    }

    public function canonicalizeSignedInfo()
    {

        $doc = $this->sigNode->ownerDocument;
        $canonicalmethod = NULL;
        if ($doc) {
            $xpath = $this->getXPathObj();
            $query = "./secdsig:SignedInfo";
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($signInfoNode = $nodeset->item(0)) {
                $query = "./secdsig:CanonicalizationMethod";
                $nodeset = $xpath->query($query, $signInfoNode);
                if ($canonNode = $nodeset->item(0)) {
                    $canonicalmethod = $canonNode->getAttribute('Algorithm');
                }
                $this->signedInfo = $this->canonicalizeData($signInfoNode, $canonicalmethod);
                return $this->signedInfo;
            }
        }
        return NULL;
    }

    public function calculateDigest($digestAlgorithm, $data)
    {
        switch ($digestAlgorithm) {
            case XMLSecurityDSig::SHA1:
                $alg = 'sha1';
                break;
            case XMLSecurityDSig::SHA256:
                $alg = 'sha256';
                break;
            case XMLSecurityDSig::SHA512:
                $alg = 'sha512';
                break;
            case XMLSecurityDSig::RIPEMD160:
                $alg = 'ripemd160';
                break;
            default:
                throw new Exception("Cannot validate digest: Unsupported Algorith <$digestAlgorithm>");
        }
        if (function_exists('hash')) {
            return base64_encode(hash($alg, $data, TRUE));
        }
        elseif (function_exists('mhash')) {
            $alg = "MHASH_" . strtoupper($alg);
            return base64_encode(mhash(constant($alg), $data));
        }
        elseif ($alg === 'sha1') {
            return base64_encode(sha1($data, TRUE));
        }
        else {
            throw new Exception('xmlseclibs is unable to calculate a digest. Maybe you need the mhash library?');
        }
    }

    public function validateDigest($refNode, $data)
    {
        $xpath = new DOMXPath($refNode->ownerDocument);
        $xpath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
        $query = 'string(./secdsig:DigestMethod/@Algorithm)';
        $digestAlgorithm = $xpath->evaluate($query, $refNode);
        $digValue = $this->calculateDigest($digestAlgorithm, $data);
        $query = 'string(./secdsig:DigestValue)';
        $digestValue = $xpath->evaluate($query, $refNode);
        return ($digValue == $digestValue);
    }

    public function processTransforms($refNode, $objData)
    {
        $data = $objData;
        $xpath = new DOMXPath($refNode->ownerDocument);
        $xpath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
        $query = './secdsig:Transforms/secdsig:Transform';
        $nodelist = $xpath->query($query, $refNode);
        $canonicalMethod = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
        $arXPath = NULL;
        $prefixList = NULL;
        foreach ($nodelist as $transform) {
            $algorithm = $transform->getAttribute("Algorithm");
            switch ($algorithm) {
                case 'http://www.w3.org/2001/10/xml-exc-c14n#':
                case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':
                    $node = $transform->firstChild;
                    while ($node) {
                        if ($node->localName == 'InclusiveNamespaces') {
                            if ($pfx = $node->getAttribute('PrefixList')) {
                                $arpfx = array();
                                $pfxlist = split(" ", $pfx);
                                foreach ($pfxlist as $pfx) {
                                    $val = trim($pfx);
                                    if (!empty($val)) {
                                        $arpfx[] = $val;
                                    }
                                }
                                if (count($arpfx) > 0) {
                                    $prefixList = $arpfx;
                                }
                            }
                            break;
                        }
                        $node = $node->nextSibling;
                    }
                case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
                case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
                    $canonicalMethod = $algorithm;
                    break;
                case 'http://www.w3.org/TR/1999/REC-xpath-19991116':
                    $node = $transform->firstChild;
                    while ($node) {
                        if ($node->localName == 'XPath') {
                            $arXPath = array();
                            $arXPath['query'] = '(.//. | .//@* | .//namespace::*)[' . $node->nodeValue . ']';
                            $arXpath['namespaces'] = array();
                            $nslist = $xpath->query('./namespace::*', $node);
                            foreach ($nslist as $nsnode) {
                                if ($nsnode->localName != "xml") {
                                    $arXPath['namespaces'][$nsnode->localName] = $nsnode->nodeValue;
                                }
                            }
                            break;
                        }
                        $node = $node->nextSibling;
                    }
                    break;
            }
        }
        if ($data instanceof DOMNode) {
            $data = $this->canonicalizeData($objData, $canonicalMethod, $arXPath, $prefixList);
        }
        return $data;
    }

    public function processRefNode($refNode)
    {
        $dataObject = NULL;
        if ($uri = $refNode->getAttribute("URI")) {
            $arUrl = parse_url($uri);
            if (empty($arUrl['path'])) {
                if ($identifier = $arUrl['fragment']) {
                    $xPath = new DOMXPath($refNode->ownerDocument);
                    if ($this->idNS && is_array($this->idNS)) {
                        foreach ($this->idNS as $nspf => $ns) {
                            $xPath->registerNamespace($nspf, $ns);
                        }
                    }
                    $iDlist = '@Id="' . $identifier . '"';
                    if (is_array($this->idKeys)) {
                        foreach ($this->idKeys as $idKey) {
                            $iDlist .= " or @$idKey='$identifier'";
                        }
                    }
                    $query = '//*[' . $iDlist . ']';
                    $dataObject = $xPath->query($query)->item(0);
                }
                else {
                    $dataObject = $refNode->ownerDocument;
                }
            }
            else {
                $dataObject = file_get_contents($arUrl);
            }
        }
        else {
            $dataObject = $refNode->ownerDocument;
        }
        $data = $this->processTransforms($refNode, $dataObject);
        if (!$this->validateDigest($refNode, $data)) {
            return FALSE;
        }

        if ($dataObject instanceof DOMNode) {
            /* Add this node to the list of validated nodes. */
            if (!empty($identifier)) {
                $this->validatedNodes[$identifier] = $dataObject;
            }
            else {
                $this->validatedNodes[] = $dataObject;
            }
        }

        return TRUE;
    }

    public function getRefNodeID($refNode)
    {
        if ($uri = $refNode->getAttribute("URI")) {
            $arUrl = parse_url($uri);
            if (empty($arUrl['path'])) {
                if ($identifier = $arUrl['fragment']) {
                    return $identifier;
                }
            }
        }
        return null;
    }

    public function getRefIDs()
    {
        $refids = array();
        $doc = $this->sigNode->ownerDocument;

        $xpath = $this->getXPathObj();
        $query = "./secdsig:SignedInfo/secdsig:Reference";
        $nodeset = $xpath->query($query, $this->sigNode);
        if ($nodeset->length == 0) {
            throw new Exception("Reference nodes not found");
        }
        foreach ($nodeset as $refNode) {
            $refids[] = $this->getRefNodeID($refNode);
        }
        return $refids;
    }

    public function validateReference()
    {
        $doc = $this->sigNode->ownerDocument;
        if (!$doc->isSameNode($this->sigNode)) {
            $this->sigNode->parentNode->removeChild($this->sigNode);
        }
        $xpath = $this->getXPathObj();
        $query = "./secdsig:SignedInfo/secdsig:Reference";
        $nodeset = $xpath->query($query, $this->sigNode);
        if ($nodeset->length == 0) {
            throw new Exception("Reference nodes not found");
        }

        /* Initialize/reset the list of validated nodes. */
        $this->validatedNodes = array();

        foreach ($nodeset as $refNode) {
            if (!$this->processRefNode($refNode)) {
                /* Clear the list of validated nodes. */
                $this->validatedNodes = NULL;
                throw new Exception("Reference validation failed");
            }
        }
        return TRUE;
    }

    private function addRefInternal($sinfoNode, $node, $algorithm, $arTransforms = NULL, $options = NULL)
    {
        $prefix = NULL;
        $prefix_ns = NULL;
        $id_name = 'Id';
        $overwrite_id = TRUE;
        $force_uri = FALSE;

        if (is_array($options)) {
            $prefix = empty($options['prefix']) ? NULL : $options['prefix'];
            $prefix_ns = empty($options['prefix_ns']) ? NULL : $options['prefix_ns'];
            $id_name = empty($options['id_name']) ? 'Id' : $options['id_name'];
            $overwrite_id = !isset($options['overwrite']) ? TRUE : (bool)$options['overwrite'];
            $force_uri = !isset($options['force_uri']) ? FALSE : (bool)$options['force_uri'];
        }

        $attname = $id_name;
        if (!empty($prefix)) {
            $attname = $prefix . ':' . $attname;
        }

        $refNode = $this->createNewSignNode('Reference');
        $sinfoNode->appendChild($refNode);

        if (!$node instanceof DOMDocument) {
            $uri = NULL;
            if (!$overwrite_id) {
                $uri = $node->getAttributeNS($prefix_ns, $attname);
            }
            if (empty($uri)) {
                $uri = XMLSecurityDSig::generate_GUID();
                $node->setAttributeNS($prefix_ns, $attname, $uri);
            }
            $refNode->setAttribute("URI", '#' . $uri);
        }
        elseif ($force_uri) {
            $refNode->setAttribute("URI", '');
        }

        $transNodes = $this->createNewSignNode('Transforms');
        $refNode->appendChild($transNodes);

        if (is_array($arTransforms)) {
            foreach ($arTransforms as $transform) {
                $transNode = $this->createNewSignNode('Transform');
                $transNodes->appendChild($transNode);
                if (is_array($transform) &&
                (!empty($transform['http://www.w3.org/TR/1999/REC-xpath-19991116'])) &&
                (!empty($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['query']))) {
                    $transNode->setAttribute('Algorithm', 'http://www.w3.org/TR/1999/REC-xpath-19991116');
                    $XPathNode = $this->createNewSignNode('XPath', $transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['query']);
                    $transNode->appendChild($XPathNode);
                    if (!empty($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['namespaces'])) {
                        foreach ($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['namespaces'] as $prefix => $namespace) {
                            $XPathNode->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:$prefix", $namespace);
                        }
                    }
                }
                else {
                    $transNode->setAttribute('Algorithm', $transform);
                }
            }
        }
        elseif (!empty($this->canonicalMethod)) {
            $transNode = $this->createNewSignNode('Transform');
            $transNodes->appendChild($transNode);
            $transNode->setAttribute('Algorithm', $this->canonicalMethod);
        }

        $canonicalData = $this->processTransforms($refNode, $node);
        $digValue = $this->calculateDigest($algorithm, $canonicalData);

        $digestMethod = $this->createNewSignNode('DigestMethod');
        $refNode->appendChild($digestMethod);
        $digestMethod->setAttribute('Algorithm', $algorithm);

        $digestValue = $this->createNewSignNode('DigestValue', $digValue);
        $refNode->appendChild($digestValue);
    }

    public function addReference($node, $algorithm, $arTransforms = NULL, $options = NULL)
    {
        if ($xpath = $this->getXPathObj()) {
            $query = "./secdsig:SignedInfo";
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($sInfo = $nodeset->item(0)) {
                $this->addRefInternal($sInfo, $node, $algorithm, $arTransforms, $options);
            }
        }
    }

    public function addReferenceList($arNodes, $algorithm, $arTransforms = NULL, $options = NULL)
    {
        if ($xpath = $this->getXPathObj()) {
            $query = "./secdsig:SignedInfo";
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($sInfo = $nodeset->item(0)) {
                foreach ($arNodes as $node) {
                    $this->addRefInternal($sInfo, $node, $algorithm, $arTransforms, $options);
                }
            }
        }
    }

    public function addObject($data, $mimetype = NULL, $encoding = NULL)
    {
        $objNode = $this->createNewSignNode('Object');
        $this->sigNode->appendChild($objNode);
        if (!empty($mimetype)) {
            $objNode->setAtribute('MimeType', $mimetype);
        }
        if (!empty($encoding)) {
            $objNode->setAttribute('Encoding', $encoding);
        }

        if ($data instanceof DOMElement) {
            $newData = $this->sigNode->ownerDocument->importNode($data, TRUE);
        }
        else {
            $newData = $this->sigNode->ownerDocument->createTextNode($data);
        }
        $objNode->appendChild($newData);

        return $objNode;
    }

    public function locateKey($node = NULL)
    {
        if (empty($node)) {
            $node = $this->sigNode;
        }
        if (!$node instanceof DOMNode) {
            return NULL;
        }
        if ($doc = $node->ownerDocument) {
            $xpath = new DOMXPath($doc);
            $xpath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
            $query = "string(./secdsig:SignedInfo/secdsig:SignatureMethod/@Algorithm)";
            $algorithm = $xpath->evaluate($query, $node);
            if ($algorithm) {
                try {
                    $objKey = new XMLSecurityKey($algorithm, array('type' => 'public'));
                }
                catch (Exception $e) {
                    return NULL;
                }
                return $objKey;
            }
        }
        return NULL;
    }

    public function verify($objKey)
    {
        $doc = $this->sigNode->ownerDocument;
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
        $query = "string(./secdsig:SignatureValue)";
        $sigValue = $xpath->evaluate($query, $this->sigNode);
        if (empty($sigValue)) {
            throw new Exception("Unable to locate SignatureValue");
        }
        return $objKey->verifySignature($this->signedInfo, base64_decode($sigValue));
    }

    public function signData($objKey, $data)
    {
        return $objKey->signData($data);
    }

    public function sign($objKey)
    {
        if ($xpath = $this->getXPathObj()) {
            $query = "./secdsig:SignedInfo";
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($sInfo = $nodeset->item(0)) {
                $query = "./secdsig:SignatureMethod";
                $nodeset = $xpath->query($query, $sInfo);
                $sMethod = $nodeset->item(0);
                $sMethod->setAttribute('Algorithm', $objKey->type);
                $data = $this->canonicalizeData($sInfo, $this->canonicalMethod);
                $sigValue = base64_encode($this->signData($objKey, $data));
                $sigValueNode = $this->createNewSignNode('SignatureValue', $sigValue);
                if ($infoSibling = $sInfo->nextSibling) {
                    $infoSibling->parentNode->insertBefore($sigValueNode, $infoSibling);
                }
                else {
                    $this->sigNode->appendChild($sigValueNode);
                }
            }
        }
    }

    public function appendCert()
    {

    }

    public function appendKey($objKey, $parent = NULL)
    {
        $objKey->serializeKey($parent);
    }


    /**
     * This function inserts the signature element.
     *
     * The signature element will be appended to the element, unless $beforeNode is specified. If $beforeNode
     * is specified, the signature element will be inserted as the last element before $beforeNode.
     *
     * @param $node  The node the signature element should be inserted into.
     * @param $beforeNode  The node the signature element should be located before.
     */
    public function insertSignature($node, $beforeNode = NULL)
    {

        $document = $node->ownerDocument;
        $signatureElement = $document->importNode($this->sigNode, TRUE);

        if ($beforeNode == NULL) {
            $node->insertBefore($signatureElement);
        }
        else {
            $node->insertBefore($signatureElement, $beforeNode);
        }
    }

    public function appendSignature($parentNode, $insertBefore = FALSE)
    {
        $beforeNode = $insertBefore ? $parentNode->firstChild : NULL;
        $this->insertSignature($parentNode, $beforeNode);
    }

    static function get509XCert($cert, $isPEMFormat = TRUE)
    {
        $certs = XMLSecurityDSig::staticGet509XCerts($cert, $isPEMFormat);
        if (!empty($certs)) {
            return $certs[0];
        }
        return '';
    }

    static function staticGet509XCerts($certs, $isPEMFormat = TRUE)
    {
        if ($isPEMFormat) {
            $data = '';
            $certlist = array();
            $arCert = explode("\n", $certs);
            $inData = FALSE;
            foreach ($arCert as $curData) {
                if (!$inData) {
                    if (strncmp($curData, '-----BEGIN CERTIFICATE', 22) == 0) {
                        $inData = TRUE;
                    }
                }
                else {
                    if (strncmp($curData, '-----END CERTIFICATE', 20) == 0) {
                        $inData = FALSE;
                        $certlist[] = $data;
                        $data = '';
                        continue;
                    }
                    $data .= trim($curData);
                }
            }
            return $certlist;
        }
        else {
            return array($certs);
        }
    }

    static function staticAdd509Cert($parentRef, $cert, $isPEMFormat = TRUE, $isURL = False, $xpath = NULL)
    {
        if ($isURL) {
            $cert = file_get_contents($cert);
        }
        if (!$parentRef instanceof DOMElement) {
            throw new Exception('Invalid parent Node parameter');
        }
        $baseDoc = $parentRef->ownerDocument;

        if (empty($xpath)) {
            $xpath = new DOMXPath($parentRef->ownerDocument);
            $xpath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
        }

        $query = "./secdsig:KeyInfo";
        $nodeset = $xpath->query($query, $parentRef);
        $keyInfo = $nodeset->item(0);
        if (!$keyInfo) {
            $inserted = FALSE;
            $keyInfo = $baseDoc->createElementNS(XMLSecurityDSig::XMLDSIGNS, 'ds:KeyInfo');

            $query = "./secdsig:Object";
            $nodeset = $xpath->query($query, $parentRef);
            if ($sObject = $nodeset->item(0)) {
                $sObject->parentNode->insertBefore($keyInfo, $sObject);
                $inserted = TRUE;
            }

            if (!$inserted) {
                $parentRef->appendChild($keyInfo);
            }
        }

        // Add all certs if there are more than one
        $certs = XMLSecurityDSig::staticGet509XCerts($cert, $isPEMFormat);

        // Atach X509 data node
        $x509DataNode = $baseDoc->createElementNS(XMLSecurityDSig::XMLDSIGNS, 'ds:X509Data');
        $keyInfo->appendChild($x509DataNode);

        // Atach all certificate nodes
        foreach ($certs as $X509Cert) {
            $x509CertNode = $baseDoc->createElementNS(XMLSecurityDSig::XMLDSIGNS, 'ds:X509Certificate', $X509Cert);
            $x509DataNode->appendChild($x509CertNode);
        }
    }

    public function add509Cert($cert, $isPEMFormat = TRUE, $isURL = False)
    {
        if ($xpath = $this->getXPathObj()) {
            self::staticAdd509Cert($this->sigNode, $cert, $isPEMFormat, $isURL, $xpath);
        }
    }

    /* This function retrieves an associative array of the validated nodes.
     *
     * The array will contain the id of the referenced node as the key and the node itself
     * as the value.
     *
     * Returns:
     *  An associative array of validated nodes or NULL if no nodes have been validated.
     */
    public function getValidatedNodes()
    {
        return $this->validatedNodes;
    }
}
