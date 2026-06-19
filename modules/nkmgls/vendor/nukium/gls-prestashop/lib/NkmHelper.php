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
if (!function_exists('nkmStripAccents')) {
    function nkmStripAccents($str)
    {
        $str = preg_replace('/[\x{00C0}\x{00C1}\x{00C2}\x{00C3}\x{00C4}\x{00C5}]/u', 'A', $str);
        $str = preg_replace('/[\x{0105}\x{0104}\x{00E0}\x{00E1}\x{00E2}\x{00E3}\x{00E4}\x{00E5}]/u', 'a', $str);
        $str = preg_replace('/[\x{00C7}\x{0106}\x{0108}\x{010A}\x{010C}]/u', 'C', $str);
        $str = preg_replace('/[\x{00E7}\x{0107}\x{0109}\x{010B}\x{010D}}]/u', 'c', $str);
        $str = preg_replace('/[\x{010E}\x{0110}]/u', 'D', $str);
        $str = preg_replace('/[\x{010F}\x{0111}]/u', 'd', $str);
        $str = preg_replace('/[\x{00C8}\x{00C9}\x{00CA}\x{00CB}\x{0112}\x{0114}\x{0116}\x{0118}\x{011A}]/u', 'E', $str);
        $str = preg_replace('/[\x{00E8}\x{00E9}\x{00EA}\x{00EB}\x{0113}\x{0115}\x{0117}\x{0119}\x{011B}]/u', 'e', $str);
        $str = preg_replace('/[\x{00CC}\x{00CD}\x{00CE}\x{00CF}\x{0128}\x{012A}\x{012C}\x{012E}\x{0130}]/u', 'I', $str);
        $str = preg_replace('/[\x{00EC}\x{00ED}\x{00EE}\x{00EF}\x{0129}\x{012B}\x{012D}\x{012F}\x{0131}]/u', 'i', $str);
        $str = preg_replace('/[\x{0142}\x{0141}\x{013E}\x{013A}]/u', 'l', $str);
        $str = preg_replace('/[\x{00F1}\x{0148}]/u', 'n', $str);
        $str = preg_replace('/[\x{00D2}\x{00D3}\x{00D4}\x{00D5}\x{00D6}\x{00D8}]/u', 'O', $str);
        $str = preg_replace('/[\x{00F2}\x{00F3}\x{00F4}\x{00F5}\x{00F6}\x{00F8}]/u', 'o', $str);
        $str = preg_replace('/[\x{0159}\x{0155}]/u', 'r', $str);
        $str = preg_replace('/[\x{015B}\x{015A}\x{0161}]/u', 's', $str);
        $str = preg_replace('/[\x{00DF}]/u', 'ss', $str);
        $str = preg_replace('/[\x{0165}]/u', 't', $str);
        $str = preg_replace('/[\x{00D9}\x{00DA}\x{00DB}\x{00DC}\x{016E}\x{0170}\x{0172}]/u', 'U', $str);
        $str = preg_replace('/[\x{00F9}\x{00FA}\x{00FB}\x{00FC}\x{016F}\x{0171}\x{0173}]/u', 'u', $str);
        $str = preg_replace('/[\x{00FD}\x{00FF}]/u', 'y', $str);
        $str = preg_replace('/[\x{017C}\x{017A}\x{017B}\x{0179}\x{017E}]/u', 'z', $str);
        $str = preg_replace('/[\x{00C6}]/u', 'AE', $str);
        $str = preg_replace('/[\x{00E6}]/u', 'ae', $str);
        $str = preg_replace('/[\x{0152}]/u', 'OE', $str);
        $str = preg_replace('/[\x{0153}]/u', 'oe', $str);
        $str = preg_replace('/[\x{0022}\x{0025}\x{0026}\x{0027}\x{00A1}\x{00A2}\x{00A3}\x{00A4}\x{00A5}\x{00A6}\x{00A7}\x{00A8}\x{00AA}\x{00AB}\x{00AC}\x{00AD}\x{00AE}\x{00AF}\x{00B0}\x{00B1}\x{00B2}\x{00B3}\x{00B4}\x{00B5}\x{00B6}\x{00B7}\x{00B8}\x{00BA}\x{00BB}\x{00BC}\x{00BD}\x{00BE}\x{00BF}]/u', ' ', $str);

        return $str;
    }
}

if (!function_exists('nkmCutSentenceMulti')) {
    function nkmCutSentenceMulti($_chaine, $_limit = 0)
    {
        $Tmp_O = [''];
        if (strlen($_chaine) > $_limit && $_limit) {
            $Tmp_Tb = explode(' ', $_chaine);
            $cpt = 0;
            foreach ($Tmp_Tb as $v) {
                if (isset($Tmp_O[$cpt])) {
                    $text = $Tmp_O[$cpt] . $v;
                } else {
                    $text = $v;
                }
                if (strlen($text) >= $_limit) {
                    ++$cpt;
                    $Tmp_O[$cpt] = '';
                }
                $Tmp_O[$cpt] .= $v . ' ';
            }
        } else {
            $Tmp_O[0] = $_chaine;
        }

        return $Tmp_O;
    }
}

if (!function_exists('nkmUdate')) {
    function nkmUdate($format = 'u', $utimestamp = null)
    {
        if (is_null($utimestamp)) {
            $utimestamp = microtime(true);
        }

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);
        $milliseconds = substr($milliseconds, 0, 2);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }
}
