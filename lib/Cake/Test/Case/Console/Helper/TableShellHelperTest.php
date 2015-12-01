<?php
App::uses("TableShellHelper", "Console/Helper");
App::uses("ConsoleOutputStub", "TestSuite/Stub");

/**
 * ProgressHelper test.
 * @property ConsoleOutputStub $consoleOutput
 * @property TableShellHelper $helper
 */
class TableShellHelperTest extends CakeTestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->consoleOutput = new ConsoleOutputStub();
        $this->helper = new TableShellHelper($this->consoleOutput);
    }

    /**
     * Test output
     *
     * @return void
     */
    public function testDefaultOutput()
    {
        $data = [
            ['Header 1', 'Header', 'Long Header'],
            ['short', 'Longish thing', 'short'],
            ['Longer thing', 'short', 'Longest Value'],
        ];
        $this->helper->output($data);
        $expected = [
            '+--------------+---------------+---------------+',
            '| <info>Header 1</info>     | <info>Header</info>        | <info>Long Header</info>   |',
            '+--------------+---------------+---------------+',
            '| short        | Longish thing | short         |',
            '| Longer thing | short         | Longest Value |',
            '+--------------+---------------+---------------+',
        ];
        $this->assertEquals($expected, $this->consoleOutput->messages());
    }
    /**
     * Test output with multibyte characters
     *
     * @return void
     */
    public function testOutputUtf8()
    {
        $data = [
            ['Header 1', 'Head', 'Long Header'],
            ['short', 'ÄÄÄÜÜÜ', 'short'],
            ['Longer thing', 'longerish', 'Longest Value'],
        ];
        $this->helper->output($data);
        $expected = [
            '+--------------+-----------+---------------+',
            '| <info>Header 1</info>     | <info>Head</info>      | <info>Long Header</info>   |',
            '+--------------+-----------+---------------+',
            '| short        | ÄÄÄÜÜÜ    | short         |',
            '| Longer thing | longerish | Longest Value |',
            '+--------------+-----------+---------------+',
        ];
        $this->assertEquals($expected, $this->consoleOutput->messages());
    }
    /**
     * Test output without headers
     *
     * @return void
     */
    public function testOutputWithoutHeaderStyle()
    {
        $data = [
            ['Header 1', 'Header', 'Long Header'],
            ['short', 'Longish thing', 'short'],
            ['Longer thing', 'short', 'Longest Value'],
        ];
        $this->helper->config(['headerStyle' => false]);
        $this->helper->output($data);
        $expected = [
            '+--------------+---------------+---------------+',
            '| Header 1     | Header        | Long Header   |',
            '+--------------+---------------+---------------+',
            '| short        | Longish thing | short         |',
            '| Longer thing | short         | Longest Value |',
            '+--------------+---------------+---------------+',
        ];
        $this->assertEquals($expected, $this->consoleOutput->messages());
    }
    /**
     * Test output with different header style
     *
     * @return void
     */
    public function testOutputWithDifferentHeaderStyle()
    {
        $data = [
            ['Header 1', 'Header', 'Long Header'],
            ['short', 'Longish thing', 'short'],
            ['Longer thing', 'short', 'Longest Value'],
        ];
        $this->helper->config(['headerStyle' => 'error']);
        $this->helper->output($data);
        $expected = [
            '+--------------+---------------+---------------+',
            '| <error>Header 1</error>     | <error>Header</error>        | <error>Long Header</error>   |',
            '+--------------+---------------+---------------+',
            '| short        | Longish thing | short         |',
            '| Longer thing | short         | Longest Value |',
            '+--------------+---------------+---------------+',
        ];
        $this->assertEquals($expected, $this->consoleOutput->messages());
    }
    /**
     * Test output without table headers
     *
     * @return void
     */
    public function testOutputWithoutHeaders()
    {
        $data = [
            ['short', 'Longish thing', 'short'],
            ['Longer thing', 'short', 'Longest Value'],
        ];
        $this->helper->config(['headers' => false]);
        $this->helper->output($data);
        $expected = [
            '+--------------+---------------+---------------+',
            '| short        | Longish thing | short         |',
            '| Longer thing | short         | Longest Value |',
            '+--------------+---------------+---------------+',
        ];
        $this->assertEquals($expected, $this->consoleOutput->messages());
    }
    /**
     * Test output with row separator
     *
     * @return void
     */
    public function testOutputWithRowSeparator()
    {
        $data = [
            ['Header 1', 'Header', 'Long Header'],
            ['short', 'Longish thing', 'short'],
            ['Longer thing', 'short', 'Longest Value']
        ];
        $this->helper->config(['rowSeparator' => true]);
        $this->helper->output($data);
        $expected = [
            '+--------------+---------------+---------------+',
            '| <info>Header 1</info>     | <info>Header</info>        | <info>Long Header</info>   |',
            '+--------------+---------------+---------------+',
            '| short        | Longish thing | short         |',
            '+--------------+---------------+---------------+',
            '| Longer thing | short         | Longest Value |',
            '+--------------+---------------+---------------+',
        ];
        $this->assertEquals($expected, $this->consoleOutput->messages());
    }
    /**
     * Test output with row separator and no headers
     *
     * @return void
     */
    public function testOutputWithRowSeparatorAndHeaders()
    {
        $data = [
            ['Header 1', 'Header', 'Long Header'],
            ['short', 'Longish thing', 'short'],
            ['Longer thing', 'short', 'Longest Value'],
        ];
        $this->helper->config(['rowSeparator' => true]);
        $this->helper->output($data);
        $expected = [
            '+--------------+---------------+---------------+',
            '| <info>Header 1</info>     | <info>Header</info>        | <info>Long Header</info>   |',
            '+--------------+---------------+---------------+',
            '| short        | Longish thing | short         |',
            '+--------------+---------------+---------------+',
            '| Longer thing | short         | Longest Value |',
            '+--------------+---------------+---------------+',
        ];
        $this->assertEquals($expected, $this->consoleOutput->messages());
    }
}