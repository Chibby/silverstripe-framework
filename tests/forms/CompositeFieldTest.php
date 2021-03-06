<?php

use SilverStripe\Dev\CSSContentParser;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\RequiredFields;

/**
 * @package framework
 * @subpackage tests
 */
class CompositeFieldTest extends SapphireTest {

	public function testFieldPosition() {
		$compositeOuter = new CompositeField(
			new TextField('A'),
			new TextField('B'),
			$compositeInner = new CompositeField(
				new TextField('C1'),
				new TextField('C2')
			),
			new TextField('D')
		);

		$this->assertEquals(0, $compositeOuter->fieldPosition('A'));
		$this->assertEquals(1, $compositeOuter->fieldPosition('B'));
		$this->assertEquals(3, $compositeOuter->fieldPosition('D'));

		$this->assertEquals(0, $compositeInner->fieldPosition('C1'));
		$this->assertEquals(1, $compositeInner->fieldPosition('C2'));

		$compositeOuter->insertBefore('B', new TextField('AB'));
		$this->assertEquals(0, $compositeOuter->fieldPosition('A'));
		$this->assertEquals(1, $compositeOuter->fieldPosition('AB'));
		$this->assertEquals(2, $compositeOuter->fieldPosition('B'));
	}

	public function testTag() {
		$div = new CompositeField(
			new TextField('A'),
			new TextField('B')
		);
		$this->assertStringStartsWith('<div', trim($div->FieldHolder()));
		$this->assertStringEndsWith('/div>', trim($div->FieldHolder()));

		$fieldset = new CompositeField();
		$fieldset->setTag('fieldset');

		$this->assertStringStartsWith('<fieldset', trim($fieldset->FieldHolder()));
		$this->assertStringEndsWith('/fieldset>', trim($fieldset->FieldHolder()));
	}

	public function testPushAndUnshift() {
		$composite = new CompositeField(
			new TextField('Middle')
		);

		$composite->push(new TextField('End'));
		/* There are now 2 fields in the set */
		$this->assertEquals(2, $composite->getChildren()->Count());
		/* The most recently added field is at the end of the set */
		$this->assertEquals('End', $composite->getChildren()->Last()->getName());

		$composite->unshift(new TextField('Beginning'));
		/* There are now 3 fields in the set */
		$this->assertEquals(3, $composite->getChildren()->Count());
		/* The most recently added field is at the beginning of the set */
		$this->assertEquals('Beginning', $composite->getChildren()->First()->getName());
	}

	public function testLegend() {
		$composite = new CompositeField(
			new TextField('A'),
			new TextField('B')
		);

		$composite->setTag('fieldset');
		$composite->setLegend('My legend');

		$parser = new CSSContentParser($composite->FieldHolder());
		$root = $parser->getBySelector('fieldset.composite');
		$legend = $parser->getBySelector('fieldset.composite legend');

		$this->assertNotNull($legend);
		$this->assertEquals('My legend', (string)$legend[0]);
	}

	public function testValidation() {
		$field = CompositeField::create(
			$fieldOne = DropdownField::create('A'),
			$fieldTwo = TextField::create('B')
		);
		$validator = new RequiredFields();
		$this->assertFalse(
			$field->validate($validator),
			"Validation fails when child is invalid"
		);
		$fieldOne->setEmptyString('empty');
		$this->assertTrue(
			$field->validate($validator),
			"Validates when children are valid"
		);
	}
}
