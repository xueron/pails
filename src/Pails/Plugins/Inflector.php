<?php
namespace Pails\Plugins;

class Inflector
{
    private $plural = array();
    private $singular = array();
    private $irregular = array();
    private $uncountable = array();

    /**
     * Inflector constructor.
     */
    public function __construct()
    {
        $this->_update_plural();
        $this->_update_singular();
        $this->_update_irregular();
        $this->_update_uncountable();
    }

    /**
     * @param $word
     * @return mixed
     */
    public function pluralize($word)
    {
        return $this->_apply_inflections($word, $this->plural);
    }

    /**
     * @param $word
     * @return mixed
     */
    public function singularize($word)
    {
        return $this->_apply_inflections($word, $this->singular);
    }

    /**
     * @param $word
     * @param $rules
     * @return mixed
     */
    private function _apply_inflections($word, $rules)
    {
        $result = $word;
        if (empty($result)) return $result;
        if (sizeof($this->uncountable) > 0) {
            foreach ($this->uncountable as $u) {
                if (preg_match("#^{$u}$#", $result)) {
                    return $result;
                }
            }
        }

        for ($i = (sizeof($rules) - 1); $i >= 0; $i--) {
            $rule = $rules[$i];
            if (preg_match($rule[0], $result)) {
                $result = preg_replace($rule[0], $rule[1], $result);
                break;
            }
        }

        return $result;
    }

    /**
     * 复数规则
     */
    private function _update_plural()
    {
        $this->_plural('/$/', 's');
        $this->_plural('/s$/i', 's');
        $this->_plural('/(ax|test)is$/i', '\1es');
        $this->_plural('/(octop|vir)us$/i', '\1i');
        $this->_plural('/(octop|vir)i$/i', '\1i');
        $this->_plural('/(alias|status)$/i', '\1es');
        $this->_plural('/(bu)s$/i', '\1ses');
        $this->_plural('/(buffal|tomat)o$/i', '\1oes');
        $this->_plural('/([ti])um$/i', '\1a');
        $this->_plural('/([ti])a$/i', '\1a');
        $this->_plural('/sis$/i', 'ses');
        $this->_plural('/(?:([^f])fe|([lr])f)$/i', '\1\2ves');
        $this->_plural('/(hive)$/i', '\1s');
        $this->_plural('/([^aeiouy]|qu)y$/i', '\1ies');
        $this->_plural('/(x|ch|ss|sh)$/i', '\1es');
        $this->_plural('/(matr|vert|ind)(?:ix|ex)$/i', '\1ices');
        $this->_plural('/(m|l)ouse$/i', '\1ice');
        $this->_plural('/(m|l)ice$/i', '\1ice');
        $this->_plural('/^(ox)$/i', '\1en');
        $this->_plural('/^(oxen)$/i', '\1');
        $this->_plural('/(quiz)$/i', '\1zes');
    }

    /**
     * 单数规则
     */
    private function _update_singular()
    {
        $this->_singular('/s$/i', '');
        $this->_singular('/(n)ews$/i', '\1ews');
        $this->_singular('/([ti])a$/i', '\1um');
        $this->_singular('/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i', '\1\2sis');
        $this->_singular('/(^analy)ses$/i', '\1sis');
        $this->_singular('/([^f])ves$/i', '\1fe');
        $this->_singular('/(hive)s$/i', '\1');
        $this->_singular('/(tive)s$/i', '\1');
        $this->_singular('/([lr])ves$/i', '\1f');
        $this->_singular('/([^aeiouy]|qu)ies$/i', '\1y');
        $this->_singular('/(s)eries$/i', '\1eries');
        $this->_singular('/(m)ovies$/i', '\1ovie');
        $this->_singular('/(x|ch|ss|sh)es$/i', '\1');
        $this->_singular('/(m|l)ice$/i', '\1ouse');
        $this->_singular('/(bus)es$/i', '\1');
        $this->_singular('/(o)es$/i', '\1');
        $this->_singular('/(shoe)s$/i', '\1');
        $this->_singular('/(cris|ax|test)es$/i', '\1is');
        $this->_singular('/(octop|vir)i$/i', '\1us');
        $this->_singular('/(alias|status)es$/i', '\1');
        $this->_singular('/^(ox)en/i', '\1');
        $this->_singular('/(vert|ind)ices$/i', '\1ex');
        $this->_singular('/(matr)ices$/i', '\1ix');
        $this->_singular('/(quiz)zes$/i', '\1');
        $this->_singular('/(database)s$/i', '\1');
    }

    /**
     * 不规则名词
     */
    private function _update_irregular()
    {
        $this->_irregular('person', 'people');
        $this->_irregular('man', 'men');
        $this->_irregular('child', 'children');
        $this->_irregular('sex', 'sexes');
        $this->_irregular('move', 'moves');
        $this->_irregular('cow', 'kine');
        $this->_irregular('zombie', 'zombies');
        $this->_irregular('woman', 'women');
        $this->_irregular('tooth', 'teeth');
        $this->_irregular('goose', 'geese');
        $this->_irregular('mouse', 'mice');
    }

    /**
     * 不可数名词
     */
    private function _update_uncountable()
    {
        $this->_uncountable('equipment');
        $this->_uncountable('information');
        $this->_uncountable('rice');
        $this->_uncountable('money');
        $this->_uncountable('species');
        $this->_uncountable('series');
        $this->_uncountable('fish');
        $this->_uncountable('sheep');
        $this->_uncountable('jeans');
    }

    /**
     * @param $rule
     * @param $replacement
     */
    private function _plural($rule, $replacement)
    {
        if (is_string($rule)) unset($this->uncountable[$rule]);
        unset($this->uncountable[$replacement]);
        $this->plural[sizeof($this->plural)] = array($rule, $replacement);
    }

    /**
     * @param $rule
     * @param $replacement
     */
    private function _singular($rule, $replacement)
    {
        if (is_string($rule)) unset($this->uncountable[$rule]);
        unset($this->uncountable[$replacement]);
        $this->singular[sizeof($this->singular)] = array($rule, $replacement);
    }

    /**
     * @param $singular
     * @param $plural
     */
    private function _irregular($singular, $plural)
    {
        unset($this->uncountable[$singular]);
        unset($this->uncountable[$plural]);
        if (strtoupper(substr($singular, 0, 1)) == strtoupper(substr($plural, 0, 1))) {
            $this->_plural('/(' . substr($singular, 0, 1) . ')' . substr($singular, 1) . '$/i', '\1' . substr($plural, 1));
            $this->_plural('/(' . substr($plural, 0, 1) . ')' . substr($plural, 1) . '$/i', '\1' . substr($plural, 1));
            $this->_singular('/(' . substr($plural, 0, 1) . ')' . substr($plural, 1) . '$/i', '\1' . substr($singular, 1));
        } else {
            $this->_plural('/' . strtoupper(substr($singular, 0, 1)) . '(?i)' . substr($singular, 1) . '$/',
                strtoupper(substr($plural, 0, 1)) . substr($plural, 1));
            $this->_plural('/' . strtolower(substr($singular, 0, 1)) . '(?i)' . substr($singular, 1) . '$/',
                strtolower(substr($plural, 0, 1)) . substr($plural, 1));
            $this->_plural('/' . strtoupper(substr($plural, 0, 1)) . '(?i)' . substr($plural, 1) . '$/',
                strtoupper(substr($plural, 0, 1)) . substr($plural, 1));
            $this->_plural('/' . strtolower(substr($plural, 0, 1)) . '(?i)' . substr($plural, 1) . '$/',
                strtolower(substr($plural, 0, 1)) . substr($plural, 1));
            $this->_singular('/' . strtoupper(substr($plural, 0, 1)) . '(?i)' . substr($plural, 1) . '$/',
                strtoupper(substr($singular, 0, 1)) . substr($singular, 1));
            $this->_singular('/' . strtolower(substr($plural, 0, 1)) . '(?i)' . substr($plural, 1) . '$/',
                strtolower(substr($singular, 0, 1)) . substr($singular, 1));
        }
    }

    /**
     * @param $word
     */
    private function _uncountable($word)
    {
        $this->uncountable[] = $word;
    }
}

