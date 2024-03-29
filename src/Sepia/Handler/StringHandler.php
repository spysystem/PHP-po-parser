<?php
namespace Sepia\PoParser\Handler;

/**
 *    Copyright (c) 2012 Raúl Ferràs raul.ferras@gmail.com
 *    All rights reserved.
 *
 *    Redistribution and use in source and binary forms, with or without
 *    modification, are permitted provided that the following conditions
 *    are met:
 *    1. Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *    2. Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *    3. Neither the name of copyright holders nor the names of its
 *       contributors may be used to endorse or promote products derived
 *       from this software without specific prior written permission.
 *
 *    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 *    ''AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED
 *    TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 *    PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL COPYRIGHT HOLDERS OR CONTRIBUTORS
 *    BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 *    CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *    POSSIBILITY OF SUCH DAMAGE.
 *
 * https://github.com/raulferras/PHP-po-parser
 */
class StringHandler implements HandlerInterface
{
    /**
     * @var string[]
     */
    protected $strings;

    /**
     * @var int
     */
    protected $total;

    /**
     * @var int
     */
    protected $line;

    /**
     * @param string $string
     */
    public function __construct($string)
    {
        $this->line = 0;
        $this->strings = explode("\n", $string);
        $this->total = count($this->strings);
    }

    /**
     * @return string|false
     */
    public function getNextLine()
    {
        if (isset($this->strings[$this->line])) {
            $result = $this->strings[$this->line];
            $this->line++;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function ended()
    {
        return ($this->line>=$this->total);
    }

    /**
     * @return bool
     */
    public function close()
    {
        $this->line = 0;

        return true;
    }


    /**
     * @inheritdoc
     *
     * @param string $output
     * @param array  $params
     */
    public function save($output, $params)
    {
    }
}
