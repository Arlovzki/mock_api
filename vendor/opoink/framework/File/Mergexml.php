<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\File;

class Mergexml {
	
	/*
	*	DOMDocument
	*/
	protected $dom;
	
	/*
	*	last DOMDocument added
	*/
	protected $lastDom;
	
	/*
	*	latest build DOMDocument
	*/
	protected $xml;
	
	/*
	*	collection of node to move 
	*	must call move() function to move each node
	*/
	protected $toMoveNodes = [];
	
	/*
	*	collection of node to remove 
	*	must call remove() function to remove each node
	*/
	protected $toRemoveNodes = [];
	
	public function __construct(){
	}
	
	/*
	*	add xml file to DOMDocument
	*	and create tmp XML file for new loop
	*/
	public function addXmlFile($XMLpath){
		$data = file_get_contents($XMLpath);
		
		$data = preg_replace('/<\!--[\s\S]*?-->/', '', $data);
		
		if(!$this->lastDom){
			$dom = new \DOMDocument('1.0', 'UTF-8');
			$dom->loadXML($data);
			$this->lastDom = $dom;
		} else {
			$dom = new \DOMDocument('1.0', 'UTF-8');
			$dom->loadXML($data);
			
			$this->lastDom = new \DOMDocument('1.0', 'UTF-8');
			$this->lastDom->loadXML($this->xml);
			
			$this->merge($dom->documentElement);
		}
		$this->xml = $this->lastDom->saveXML();
		return $this;
	}
	
	/*
	*	merge all xml file that was been added
	*	if xml:id already exist in lastDom
	*	the weight of node will be used to compare
	*	if the weight of new node is greater than last node
	*	then last node will be replaced by new node
	*	if nodeName is move or remove, it will go to queued node and will execute after all 
	*	xml files has been added.
	*/
	protected function merge($node, $nodeId='html'){
		$newDomId = $node->getAttribute('xml:id');
		$newDomWeight = $node->getAttribute('weight');
		
		$lastDom = $this->lastDom->getElementById($nodeId);
		
		$lastDomId = $lastDom->getAttribute('xml:id');
		$lastDomWeight = $lastDom->getAttribute('weight');
		
		if(($newDomWeight > $lastDomWeight) && ($lastDom->nodeName == $node->nodeName)){
			$nodeToInsert = $this->lastDom->importNode($node, true);
			$lastDom->parentNode->replaceChild($nodeToInsert, $lastDom);
		} elseif($newDomWeight == $lastDomWeight) {
			if($node->hasChildNodes()){
				foreach ($node->childNodes as $childNode) {
					if($childNode->nodeName === '#text'){
						continue;
					}
					if($childNode->nodeName === 'move'){
						$this->toMoveNodes[] = $childNode;
						continue;
					}
					if($childNode->nodeName === 'remove'){
						$this->toRemoveNodes[] = $childNode;
						continue;
					}
					$childNodeId = $childNode->getAttribute('xml:id');
					$isExists = $this->lastDom->getElementById($childNodeId);
					
					if(!$isExists){
						$nodePosition = $this->getNodePositionFromAttribute($childNode);
						$this->insertOrAppendChild($childNode, $lastDom, $nodePosition);
					} else {
						$this->merge($childNode, $childNodeId);
					}
				}
			}
		}
	}
	
	/*
	*	move all collected nodes
	*/
	public function remove(){
		if($this->toRemoveNodes > 0){
			$this->lastDom = new \DOMDocument('1.0', 'UTF-8');
			$this->lastDom->loadXML($this->xml);
			
			foreach($this->toRemoveNodes as $node){
				$this->removeChild($node);
			}
			$this->xml = $this->lastDom->saveXML();
			$this->toRemoveNodes = [];
		}
		return $this;
	}
	/*
	*	remove all collected node
	*/
	protected function removeChild($node){
		$divToRemoveId = $node->getAttribute('divToRemove');
		$divToRemoveNode = $this->lastDom->getElementById($divToRemoveId);
		if($divToRemoveNode){
			$divToRemoveNode->parentNode->removeChild($divToRemoveNode);
		}
	}
	
	/*
	*	move all collected nodes
	*/
	public function move(){
		if($this->toMoveNodes > 0){
			$this->lastDom = new \DOMDocument('1.0', 'UTF-8');
			$this->lastDom->loadXML($this->xml);
			
			foreach($this->toMoveNodes as $node){
				$this->moveChildNode($node);
			}
			$this->xml = $this->lastDom->saveXML();
			$this->toMoveNodes = [];
		}
		return $this;
	}
	/*
	*	move child node from one parent node 
	*	to another parent node
	*/
	protected function moveChildNode($node){
		$nodePosition = $this->getNodePositionFromAttribute($node);
		$divToMoveId = $node->getAttribute('divToMove');
		$moveToId = $node->getAttribute('moveTo');
		
		$divToMoveNode = $this->lastDom->getElementById($divToMoveId);
		$moveToIdNode = $this->lastDom->getElementById($moveToId);
		if($divToMoveNode && $moveToIdNode){
			$this->insertOrAppendChild($divToMoveNode, $moveToIdNode, $nodePosition);			
		}
	}
	
	/*
	*	insert or append a new child node found from
	*	newly added xml file
	*/
	protected function insertOrAppendChild($childNode, $lastDom, $nodePosition){
		$nodeToInsert = $this->lastDom->importNode($childNode, true);
		if($nodePosition['type'] == 'after' && $nodePosition['value'] == '-'){
			$lastDom->appendChild($nodeToInsert);
		}
		elseif($nodePosition['type'] == 'after' && $nodePosition['value'] != '-'){
			$refnode = $this->lastDom->getElementById($nodePosition['value']);
			if($refnode){
				try{
					$lastDom->insertBefore($nodeToInsert, $refnode->nextSibling);
				} catch (Exception $e){
					$lastDom->appendChild($nodeToInsert);
				}
			}
		}
		elseif($nodePosition['type'] == 'before' && $nodePosition['value'] == '-'){
			try{
				$lastDom->insertBefore($nodeToInsert, $lastDom->firstChild);
			} catch (Exception $e){
				$lastDom->appendChild($nodeToInsert);
			}
		}
		elseif($nodePosition['type'] == 'before' && $nodePosition['value'] != '-'){
			$refnode = $this->lastDom->getElementById($nodePosition['value']);
			if($refnode){
				try{
					$lastDom->insertBefore($nodeToInsert, $refnode);
				} catch (Exception $e){
					$lastDom->appendChild($nodeToInsert);
				}
			}
		}
	}
	
	/*
	*	return array
	*	for the positioning of each node child
	*/
	protected function getNodePositionFromAttribute($node){
		$position = [
			'type' => 'after',
			'value' => '-',
		];
		if($node->hasAttributes()){
			foreach($node->attributes as $key => $val){
				if($key == 'before'){
					$position['type'] = 'before';
					$position['value'] = $val->value;
				} elseif($key == 'after'){
					$position['type'] = 'after';
					$position['value'] = $val->value;
				}
			}
		}
		return $position;
	}
	
	/*
	*	save generated XML to a file
	*	$filename | file name to save
	*/
	public function save($filename=null){
		if($filename){
			$dom = new \DOMDocument('1.0', 'UTF-8');
			$dom->loadXML($this->xml);
			$dom->save($filename);
		}
		return $this;
	}
	
	public function getXml(){
		$this->move();
		$this->remove();
		return $this->xml;
	}
	
	/*
	*	echo back an xml to browser
	*/
	public function getXmlEcho(){
		$this->move();
		$this->remove();
		header ("Content-Type:text/plain");
		echo $this->xml;
		die;
	}
	
	/*
	*	todo: 
	*	return array
	*	of merged node xml files
	*/
	public function toArray(){
		
	}
}
?>