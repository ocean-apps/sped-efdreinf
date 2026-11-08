<?php

namespace NFePHP\EFDReinf\Factories;

/**
 * Class EFD-Reinf EvtServTom Event R-2010 constructor
 *
 * @category  Library
 * @package   NFePHP\EFDReinf
 * @copyright NFePHP Copyright (c) 2017 - 2021
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @link      http://github.com/nfephp-org/sped-efdreinf for the canonical source repository
 */

use NFePHP\EFDReinf\Common\Factory;
use NFePHP\EFDReinf\Common\FactoryInterface;
use NFePHP\EFDReinf\Common\FactoryId;
use NFePHP\Common\Certificate;
use NFePHP\Common\Strings;
use NFePHP\EFDReinf\Factories\Traits\FormatNumber;
use NFePHP\EFDReinf\Factories\Traits\RegraNomeValido;
use stdClass;

class EvtServTom extends Factory implements FactoryInterface
{
    use FormatNumber, RegraNomeValido;

    /**
     * Constructor
     * @param string $config
     * @param stdClass $std
     * @param Certificate $certificate
     * @param string $data
     */
    public function __construct(
        $config,
        stdClass $std,
        Certificate $certificate = null,
        $data = null
    ) {
        $params = new \stdClass();
        $params->evtName = 'evtTomadorServicos';
        $params->evtTag = 'evtServTom';
        $params->evtAlias = 'R-2010';
        parent::__construct($config, $std, $params, $certificate, $data);
    }

    /**
     * Formats BCMath decimal strings without converting to float.
     * The EFD-Reinf XSD requires a comma decimal separator.
     */
    protected static function formatDecimal($value, int $precision = 2, string $separator = ','): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value) && !is_int($value)) {
            throw new \InvalidArgumentException('Monetary values must be decimal strings or integers.');
        }

        $value = trim((string) $value);

        if (!preg_match('/^[+]?[0-9]+(?:\.[0-9]+)?$/D', $value)) {
            throw new \InvalidArgumentException('Invalid non-negative monetary value: ' . $value);
        }

        $rounded = bcround($value, $precision);
        $parts = explode('.', $rounded, 2);
        $integer = ltrim($parts[0], '0');
        $integer = $integer === '' ? '0' : $integer;
        $fraction = str_pad($parts[1] ?? '', $precision, '0');

        if (strlen($integer) > 12) {
            throw new \InvalidArgumentException('Monetary value exceeds the 12-digit XSD integer limit.');
        }

        return $integer . $separator . $fraction;
    }

    /**
     * Node constructor
     */
    protected function toNode()
    {
        $ideContri = $this->node->getElementsByTagName('ideContri')->item(0);
        //o idEvento pode variar de evento para evento
        //então cada factory individualmente terá de construir o seu
        $ideEvento = $this->dom->createElement("ideEvento");
        $this->dom->addChild(
            $ideEvento,
            "indRetif",
            $this->std->indretif,
            true
        );
        if ($this->std->indretif == 2 && empty($this->std->nrrecibo)) {
            throw new \Exception("Para retificar o evento DEVE ser informado o "
                . "número do RECIBO do evento anterior que está retificando.");
        }
        $this->dom->addChild(
            $ideEvento,
            "nrRecibo",
            !empty($this->std->nrrecibo) ? $this->std->nrrecibo : null,
            $this->std->indretif == 2 ? true : false
        );
        $this->dom->addChild(
            $ideEvento,
            "perApur",
            $this->std->perapur,
            true
        );
        $this->dom->addChild(
            $ideEvento,
            "tpAmb",
            $this->tpAmb,
            true
        );
        $this->dom->addChild(
            $ideEvento,
            "procEmi",
            $this->procEmi,
            true
        );
        $this->dom->addChild(
            $ideEvento,
            "verProc",
            $this->verProc,
            true
        );
        $this->node->insertBefore($ideEvento, $ideContri);
        $info = $this->dom->createElement("infoServTom");
        $ideEstabObra = $this->dom->createElement("ideEstabObra");
        $this->dom->addChild(
            $ideEstabObra,
            "tpInscEstab",
            $this->std->tpinscestab,
            true
        );
        $this->dom->addChild(
            $ideEstabObra,
            "nrInscEstab",
            $this->std->nrinscestab,
            true
        );
        $this->dom->addChild(
            $ideEstabObra,
            "indObra",
            $this->std->indobra,
            true
        );
        $idePrestServ = $this->dom->createElement("idePrestServ");
        $this->dom->addChild(
            $idePrestServ,
            "cnpjPrestador",
            $this->std->cnpjprestador,
            true
        );
        $this->dom->addChild(
            $idePrestServ,
            "vlrTotalBruto",
            self::formatDecimal($this->std->vlrtotalbruto, 2, $this->decimalSeparator),
            true
        );
        $this->dom->addChild(
            $idePrestServ,
            "vlrTotalBaseRet",
            self::formatDecimal($this->std->vlrtotalbaseret, 2, $this->decimalSeparator),
            true
        );
        $this->dom->addChild(
            $idePrestServ,
            "vlrTotalRetPrinc",
            self::formatDecimal($this->std->vlrtotalretprinc, 2, $this->decimalSeparator),
            true
        );
        $this->dom->addChild(
            $idePrestServ,
            "vlrTotalRetAdic",
            self::formatDecimal($this->std->vlrtotalretadic ?? null, 2, $this->decimalSeparator),
            false
        );
        $this->dom->addChild(
            $idePrestServ,
            "vlrTotalNRetPrinc",
            self::formatDecimal($this->std->vlrtotalnretprinc ?? null, 2, $this->decimalSeparator),
            false
        );
        $this->dom->addChild(
            $idePrestServ,
            "vlrTotalNRetAdic",
            self::formatDecimal($this->std->vlrtotalnretadic ?? null, 2, $this->decimalSeparator),
            false
        );
        $this->dom->addChild(
            $idePrestServ,
            "indCPRB",
            $this->std->indcprb,
            true
        );
        foreach ($this->std->nfs as $n) {
            $nfs = $this->dom->createElement("nfs");
            $this->dom->addChild(
                $nfs,
                "serie",
                $n->serie,
                true
            );
            $this->dom->addChild(
                $nfs,
                "numDocto",
                $n->numdocto,
                true
            );
            $this->dom->addChild(
                $nfs,
                "dtEmissaoNF",
                $n->dtemissaonf,
                true
            );
            $this->dom->addChild(
                $nfs,
                "vlrBruto",
                self::formatDecimal($n->vlrbruto, 2, $this->decimalSeparator),
                true
            );
            $this->dom->addChild(
                $nfs,
                "obs",
                !empty($n->obs) ? Strings::replaceUnacceptableCharacters($n->obs) : null,
                false
            );
            foreach ($n->infotpserv as $its) {
                $infoTpServ = $this->dom->createElement("infoTpServ");
                $this->dom->addChild(
                    $infoTpServ,
                    "tpServico",
                    $its->tpservico,
                    true
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrBaseRet",
                    self::formatDecimal($its->vlrbaseret, 2, $this->decimalSeparator),
                    true
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrRetencao",
                    self::formatDecimal($its->vlrretencao, 2, $this->decimalSeparator),
                    true
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrRetSub",
                    self::formatDecimal($its->vlrretsub ?? null, 2, $this->decimalSeparator),
                    false
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrNRetPrinc",
                    self::formatDecimal($its->vlrnretprinc ?? null, 2, $this->decimalSeparator),
                    false
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrServicos15",
                    self::formatDecimal($its->vlrservicos15 ?? null, 2, $this->decimalSeparator),
                    false
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrServicos20",
                    self::formatDecimal($its->vlrservicos20 ?? null, 2, $this->decimalSeparator),
                    false
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrServicos25",
                    self::formatDecimal($its->vlrservicos25 ?? null, 2, $this->decimalSeparator),
                    false
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrAdicional",
                    self::formatDecimal($its->vlradicional ?? null, 2, $this->decimalSeparator),
                    false
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrNRetAdic",
                    self::formatDecimal($its->vlrnretadic ?? null, 2, $this->decimalSeparator),
                    false
                );
                $nfs->appendChild($infoTpServ);
            }
            $idePrestServ->appendChild($nfs);
        }

        if (!empty($this->std->infoprocretpr)) {
            foreach ($this->std->infoprocretpr as $irp) {
                $infoProcRetPr = $this->dom->createElement("infoProcRetPr");
                $this->dom->addChild(
                    $infoProcRetPr,
                    "tpProcRetPrinc",
                    $irp->tpprocretprinc,
                    true
                );
                $this->dom->addChild(
                    $infoProcRetPr,
                    "nrProcRetPrinc",
                    $irp->nrprocretprinc,
                    true
                );
                $this->dom->addChild(
                    $infoProcRetPr,
                    "codSuspPrinc",
                    !empty($irp->codsuspprinc) ? $irp->codsuspprinc : null,
                    false
                );
                $this->dom->addChild(
                    $infoProcRetPr,
                    "valorPrinc",
                    self::formatDecimal($irp->valorprinc, 2, $this->decimalSeparator),
                    true
                );
                $idePrestServ->appendChild($infoProcRetPr);
            }
        }
        if (!empty($this->std->infoprocretad)) {
            foreach ($this->std->infoprocretad as $rad) {
                $infoProcRetAd = $this->dom->createElement("infoProcRetAd");
                $this->dom->addChild(
                    $infoProcRetAd,
                    "tpProcRetAdic",
                    $rad->tpprocretadic,
                    true
                );
                $this->dom->addChild(
                    $infoProcRetAd,
                    "nrProcRetAdic",
                    $rad->nrprocretadic,
                    true
                );
                $this->dom->addChild(
                    $infoProcRetAd,
                    "codSuspAdic",
                    !empty($rad->codsuspadic) ? $rad->codsuspadic : null,
                    false
                );
                $this->dom->addChild(
                    $infoProcRetAd,
                    "valorAdic",
                    self::formatDecimal($rad->valoradic, 2, $this->decimalSeparator),
                    true
                );
                $idePrestServ->appendChild($infoProcRetAd);
            }
        }
        $ideEstabObra->appendChild($idePrestServ);
        $info->appendChild($ideEstabObra);
        $this->node->appendChild($info);
        $this->reinf->appendChild($this->node);
        //$this->xml = $this->dom->saveXML($this->reinf);
        $this->sign($this->evtTag);
    }
}
