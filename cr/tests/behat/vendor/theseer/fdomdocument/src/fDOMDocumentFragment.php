<?php
/**
 * Copyright (c) 2010-2013 Arne Blankerts <arne@blankerts.de>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 *   * Neither the name of Arne Blankerts nor the names of contributors
 *     may be used to endorse or promote products derived from this software
 *     without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT  * NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER ORCONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 *
 * @category  PHP
 * @package   TheSeer\fDOM
 * @author    Arne Blankerts <arne@blankerts.de>
 * @copyright Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://github.com/theseer/fdomdocument
 *
 */

namespace TheSeer\fDOM {

    /**
     * fDOMDocumentFragment
     *
     * @category  PHP
     * @package   TheSeer\fDOM
     * @author    Arne Blankerts <arne@blankerts.de>
     * @access    public
     * @property  fDOMDocument $ownerDocument
     *
     */
    class fDOMDocumentFragment extends \DOMDocumentFragment {

        /**
         * @return string
         */
        public function __toString() {
            return $this->ownerDocument->saveXML($this);
        }

        /**
         * Wrapper to standard method with exception support
         *
         * @param string $str Data string to parse and append
         *
         * @throws fDOMException
         *
         * @return bool true on success
         */
        public function appendXML($str) {
            if (!parent::appendXML($str)) {
                throw new fDOMException('Appending xml string failed', fDOMException::ParseError);
            }
            return true;
        }

        /**
         * Create a new element and append it
         *
         * @param string $name     Name of not element to create
         * @param string $content  Optional content to be set
         *
         * @return fDOMElement Reference to created fDOMElement
         */
        public function appendElement($name, $content = null) {
            $node = $this->ownerDocument->createElement($name, $content);
            $this->appendChild($node);
            return $node;
        }

        /**
         * Create a new element in given namespace and append it
         *
         * @param string $ns       Namespace of node to create
         * @param string $name     Name of not element to create
         * @param string $content  Optional content to be set
         *
         * @return fDOMElement Reference to created fDOMElement
         */
        public function appendElementNS($ns, $name, $content = null) {
            $node = $this->ownerDocument->createElementNS($ns, $name, $content);
            $this->appendChild($node);
            return $node;
        }

        /**
         * Create a new element in given namespace and append it
         *
         * @param string $prefix   Namespace prefix for node to create
         * @param string $name     Name of not element to create
         * @param string $content  Optional content to be set
         *
         * @return fDOMElement Reference to created fDOMElement
         */
        public function appendElementPrefix($prefix, $name, $content = null) {
            $node = $this->ownerDocument->createElementPrefix($prefix, $name, $content);
            $this->appendChild($node);
            return $node;
        }

        /**
         * Create a new text node and append it
         *
         * @param string $content Text content to be added
         *
         * @return \DOMText
         */
        public function appendTextNode($content) {
            $text = $this->ownerDocument->createTextNode($content);
            $this->appendChild($text);
            return $text;
        }

        /**
         * Check if the given node is in the same document
         *
         * @param \DOMNode $node Node to compare with
         *
         * @return boolean true on match, false if they differ
         *
         */
        public function inSameDocument(\DOMNode $node) {
            return $this->ownerDocument->inSameDocument($node);
        }


        /**
         * Forward to fDomDocument->query()
         *
         * @param string   $q               XPath to use
         * @param \DOMNode $ctx             \DOMNode to overwrite context
         * @param boolean  $registerNodeNS  Register flag pass thru
         *
         * @return \DomNodeList
         */
        public function query($q, \DOMNode $ctx = null, $registerNodeNS = true) {
            return $this->ownerDocument->query($q, $ctx ? $ctx : $this, $registerNodeNS);
        }

        /**
         * Forward to fDomDocument->queryOne()
         *
         * @param string   $q               XPath to use
         * @param \DOMNode $ctx             (optional) \DOMNode to overwrite context
         * @param boolean  $registerNodeNS  Register flag pass thru
         *
         * @return mixed
         */
        public function queryOne($q, \DOMNode $ctx = null, $registerNodeNS = true) {
            return $this->ownerDocument->queryOne($q, $ctx ? $ctx : $this, $registerNodeNS);
        }

        /**
         * Forward to fDomDocument->select()
         *
         * @param string   $selector A CSS Level 3 Selector string
         * @param \DOMNode $ctx
         * @param bool     $registerNodeNS
         *
         * @return \DOMNodeList
         */
        public function select($selector, \DOMNode $ctx = null, $registerNodeNS = true) {
            return $this->ownerDocument->select($selector, $ctx ? $ctx : $this, $registerNodeNS);
        }

    } // fDOMDocumentFragment

}
