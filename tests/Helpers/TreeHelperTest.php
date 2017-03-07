<?php

use App\Models\Leaf;
use App\Models\User as User;
use App\Http\Helpers\TreeHelper;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class TreeHelperTest extends TestCase {

    use DatabaseTransactions;

    /** 
     * @test
     */
	public function treeHelper_ReturnsEmptyStringWhenLeafDatabaseEmpty() // assuming our tables are already seeded
	{
		$html = TreeHelper::printTree();
		$this->assertEmpty($html);
	}

    /** 
     * @test
     */
	public function treeHelper_ReturnsNonEmptyMarkupIfTreeNotEmpty() // assuming our tables are already seeded
	{
		$leaves = $this->seedLeaves();
		$html = TreeHelper::printTree();
		$this->assertNotEmpty($html);
	}

    /** 
     * @test
     */
	public function treeHelper_SetsCorrectSelectedLeaf()
	{
		$leaves = $this->seedLeaves();
		$leaf_ids = [ $leaves[0]->id, $leaves[2]->id, $leaves[4]->id ];

		foreach ($leaf_ids as $leaf_id) {
			$html = TreeHelper::printTree(null, array('selected_leafs' => $leaf_id));
			$dom = new DOMDocument();
			$dom->loadHTML($html);
			$xpath = new DOMXPath($dom);
			$nodes = $xpath->query('//li[@data-leaf_id="'.$leaf_id.'"]');

			$this->assertEquals($nodes->length, 1);

			foreach ($nodes as $node){
				$attributes = json_decode($node->getAttribute('data-jstree'));
				$this->assertObjectHasAttribute('selected', $attributes);
				$this->assertObjectHasAttribute('opened', $attributes);
				$this->assertTrue($attributes->selected);
				$this->assertTrue($attributes->opened);
			}
		}
	}

    /** 
     * @test
     */
	public function treeHelper_SetsCorrectSelectedLeafs()
	{
		$leaves = $this->seedLeaves();
		$leaf_ids = [ $leaves[0]->id, $leaves[2]->id, $leaves[4]->id ];

		$html = TreeHelper::printTree(null, array('selected_leafs' => $leaf_ids));
		$dom = new DOMDocument();
		$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);

		foreach ($leaf_ids as $leaf_id) {
			$nodes = $xpath->query('//li[@data-leaf_id="'.$leaf_id.'"]');
			$this->assertEquals($nodes->length, 1);

			foreach ($nodes as $node){
				$attributes = json_decode($node->getAttribute('data-jstree'));
				$this->assertObjectHasAttribute('selected', $attributes);
				$this->assertObjectHasAttribute('opened', $attributes);
				$this->assertTrue($attributes->selected);
				$this->assertTrue($attributes->opened);
			}
		}
	}

    /** 
     * @test
     */
	public function treeHelper_ReturnsSubtreeWhenTreeIDSpecified()
	{
		$leaves = $this->seedLeaves();
		$leaf_id = $leaves[0]->id;

		$html = TreeHelper::printTree($leaf_id);

		$dom = new DOMDocument();
		$dom->loadHTML($html);

		$xpath = new DOMXPath($dom);
		$nodes = $xpath->query('/html/body/ul/li');

		$this->assertEquals($nodes->length, 1);
		foreach ($nodes as $node) {
			$this->assertEquals($node->getAttribute('data-leaf_id'), $leaf_id);
			break;
		}
	}

    /** 
     * @test
     */	
	public function treeHelper_ReturnsSubtreeWhenTreeCollectionSpecified()
	{
		$leaves = $this->seedLeaves();
		$leaf_id = $leaves[0]->id;

		$tree = $leaves[0]->getDescendantsAndSelf()->toHierarchy();
		$html = TreeHelper::printTree($tree);

		$dom = new DOMDocument();
		$dom->loadHTML($html);

		$xpath = new DOMXPath($dom);
		$nodes = $xpath->query('/html/body/ul/li');

		$this->assertEquals($nodes->length, 1);
		foreach ($nodes as $node) {
			$this->assertEquals($node->getAttribute('data-leaf_id'), $leaf_id);
			break;
		}
	}



	private function seedLeaves(){
		$leaves = factory('App\Models\Leaf', 10)->create()->each(function($leaf){
			$location = factory('App\Models\Location')->create();

			$leaf->location_id = $location->id;
			$leaf->save();
		});

		for($i=0; $i<=5; $i++){
			$leaves[$i+1]->makeChildOf($leaves[$i]);
		}

		return $leaves;
	}
}
