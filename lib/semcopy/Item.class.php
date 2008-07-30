<?
/*
* Item.class.php
* This class implements the Item object used by the DataTree class
* Part of Alternative Copy Mechanism (ACM) Version 0.5
* Copyright (C) 2008  Dirk Oelkers <d.oelkers@fh-wolfenbuettel.de>

* written by Dirk Oelkers <d.oelkers@fh-wolfenbuettel.de>

* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.

* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.

* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/
class Item
{

	//item Type const
	const ROOT = 0;  // root item can be seminar for example
	const DOWNLOAD_FOLDER = 1; // table folder
	const GROUP_FOLDER = 2; // table folder
	const DOCUMENT = 3; // table document
	const HYPERLINK = 4; // table document
	const GROUP_DATA = 5; //table statusgruppen
	const ISSUE_FOLDER = 6; // table folder
	const ISSUE_DATA = 7; // table themen
	const SCHEDULE = 8;  // table termine
	const ISSUE_DISCUSSION = 9; // table px_topics,themen
	const DISCUSSION = 10; // table px_topics

	private $parent;
	private $children;

	private $num_of_children; // does not include subtrees

	private $num_of_parents; //just the direct parents of this item
	
	private $num_of_fields;

	// assoc array containing the items data title,name,description ...
	private $data;

	// this variable is used, if $this->itemType == Item::GROUP_FOLDER
	// to store the values from statusgruppen related to the folder
	//private $groupData;

	private $itemType;

	private $hasBeenCopied;

	function __construct( $parent=null, $itemType=Item::ROOT )
	{
		if ( $parent )
		{
			$this->parent[0] = $parent;

			$this->parent[0]->addChild( $this );
		}

		if ( ! $itemType )
		{
			$this->itemType = Item::ROOT;
		}
		else
		{
			$this->itemType = $itemType;
		}

		$this->children = null;
		$this->num_of_children = 0;
		$this->hasBeenCopied=false;
	}

	function getParent( $number=0 )
	{
		return $this->parent[$number];
	}

	function getParentsPerItemType( $itemType )
	{
		$result=null;
		$num_of_parents = 0;

		for($i=0;$i<$this->$num_of_parents;$i++)
		{
			$parentItem = $this->parent[$i];
			if ( $parentItem->getItemType() == $itemType )
			{
				$result[] = $parentItem;
				$num_of_parents++;
				$result["num"] = $num_of_parents;
			}

		}
		
		return $result;
	}

	function getChildren()
	{
		return $this->children;
	}

	function getChildrenPerItemType( $itemType )
	{
		$result=null;
		$num_of_children = 0;

		for($i=0;$i<$this->num_of_children;$i++)
		{
			$child = $this->children[$i];
			if ($child->getItemType() == $itemType )
			{
				$result[] = $child;
				$num_of_children++;
				$result["num_children"] = $num_of_children;
			}

		}
		return $result;
	}


	function setParent( $item , $number=0)
	{
		$this->parent[$number] = $item;
	}

	//add an new parent for this item
	// do it only, if this item does not have the given item as parent
	function addParent( $item )
	{
		$i=0;
		foreach( $this->parent as $parentItem )
		{
			if ( $parentItem == $item)
			{
				return;
			}
			$i++;
		}
		$this->parent[ $i ]=$item;
	}

	function addChild( $item )
	{
		$this->children[] = $item;
		$this->num_of_children ++;
	}

	// overwrite the data array of this item
	function setData( $data, $metadata=null )
	{
		if ( ! $metadata )
		{
			$this->data = $data;
			return;
		}

		$this->num_of_fields = $metadata["num_fields"];

		for($i=0;$i<$this->num_of_fields;$i++)
		{
			$this->data[ $metadata[$i]["name"] ][] = $data[$i];
			$this->data[ $metadata[$i]["name"] ]["type"] = $metadata[$i]["type"];
		}

	}

	function getData()
	{
		return $this->data;
	}

	function getItemType()
	{
		return $this->itemType;
	}

	function setItemType( $itemType )
	{
		$this->itemType = $itemType;
	}

	function getFieldValue( $fieldName )
	{
		$value = $this->data[ $fieldName ][0];
		return $value;
	}

	function getFieldType( $fieldName )
	{
		$type = $this->data[ $fieldName ]["type"];
		return $type;
	}

	function getFieldNameList()
	{
		foreach ( $this->data as $fieldName => $val )
		{
			$fieldNameList[] = $fieldName;
		}
		return $fieldNameList;
	}

	function getNumOfFields()
	{
		return $this->num_of_fields;
	}

	function getNumOfChildren()
	{
		return $this->num_of_children;
	}

	function hasChildren()
	{
		$result = ( $this->num_of_children > 0 );

		return (boolean) $result;
	}

	function isFolder()
	{
		$result = ( $this->itemType == Item::DOWNLOAD_FOLDER || $this->itemType == Item::GROUP_FOLDER  );

		return (boolean) $result;
	}

	function isCopied()
	{
		return $this->hasBeenCopied;
	}

	function setCopied( $status=false )
	{
		$this->hasBeenCopied = $status;
	}
}

?>