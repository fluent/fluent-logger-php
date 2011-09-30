<?php
/*
 * The MIT License
 *
 * Copyright (c) 2011 Shuhei Tanuma
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Fluent;

class Event
{
	protected $category;
	protected $accessor = array();
	protected $values = array();
	
	public function __construct($category, $options)
	{
		$args = func_get_args();
		
		$this->category = $category;
		foreach($options as $option) {
			$this->accessor[$option] = true;
		}
	}
	
	public function with($event)
	{
		if ($event instanceof Event) {
			$this->values = array_merge($this->values, $event->getValues());
			return $this;
		} else {
			throw new Exception("not implemented");
		}
	}
	
	public function getValues()
	{
		return $this->values;
	}
	
	public function post()
	{
		var_dump($this);
	}
	
	public function __call($key, $options)
	{
		if (isset($this->accessor[$key])) {
			$this->values[$key] = $options[0];
			return $this;
		} else {
			throw new \Exception("unexpected accessor name {$key}");
		}
	}
}