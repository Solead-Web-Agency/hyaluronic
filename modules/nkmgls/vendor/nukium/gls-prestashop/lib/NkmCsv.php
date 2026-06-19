<?php
/**
 *  Module made by Nukium
 *
 *  @author    Nukium
 *  @copyright 2023 Nukium SAS
 *  @license   All rights reserved
 *
 * ███    ██ ██    ██ ██   ██ ██ ██    ██ ███    ███
 * ████   ██ ██    ██ ██  ██  ██ ██    ██ ████  ████
 * ██ ██  ██ ██    ██ █████   ██ ██    ██ ██ ████ ██
 * ██  ██ ██ ██    ██ ██  ██  ██ ██    ██ ██  ██  ██
 * ██   ████  ██████  ██   ██ ██  ██████  ██      ██
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
class NkmCsv
{
    public $csvDelimeter = "\t";
    public $csvLine = "\r\n";
    public $csvCapsule = '"';

    private $csvTemplate = [];
    private $csvCollection = [];
    private $csvDocument;

    public $reader = null;

    public function __construct()
    {
        $this->reader = new NkmCSVReader();
    }

    public function createTemplate($arr = [])
    {
        $this->csvTemplate = $arr;
    }

    public function addEntry($arr = [])
    {
        foreach ($arr as $Index => $Value) {
            $arr[$Index] = $this->csvCapsule . str_replace($this->csvCapsule, $this->csvCapsule . $this->csvCapsule, $Value) . $this->csvCapsule;
        }
        $this->csvCollection[] = $arr;
    }

    public function buildDoc()
    {
        $docLine = '';
        $csvTemplate = $this->csvTemplate;

        foreach ($csvTemplate as $Index => $Title) {
            $csvTemplate[$Index] = $this->csvCapsule . str_replace($this->csvCapsule, $this->csvCapsule . $this->csvCapsule, $Title) . $this->csvCapsule;
        }
        $docLine .= implode($this->csvDelimeter, $csvTemplate) . $this->csvLine;

        foreach ($this->csvCollection as $csvCollectionItem) {
            $collectionDeposit = [];
            foreach ($csvTemplate as $Index => $Title) {
                $collectionDeposit[] = $csvCollectionItem[$Index];
            }

            $docLine .= implode($this->csvDelimeter, $collectionDeposit) . $this->csvLine;
        }

        return $docLine;
    }
}
