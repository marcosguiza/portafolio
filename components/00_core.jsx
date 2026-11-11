const { useState, useEffect, useMemo, useRef, createContext, useContext } = React;

// ---------- brand config ----------
const BRAND = Object.assign({
  brand: 'skywave',
  dataKey: 'SKYWAVE_DATA',
  logoSrc: 'assets/skywave.png',
  logoHeight: 40,
  portfolioLabel: 'LATAM · Portfolio 2026',
  langs: ['es', 'en', 'pt'],
}, window.BRAND_CONFIG || {});

// ---------- contexts ----------
const LangCtx = createContext({ lang: 'es', setLang: () => {} });
const ThemeCtx = createContext(BRAND);

const useLang = () => useContext(LangCtx);
const useTheme = () => useContext(ThemeCtx);

// ---------- i18n ----------
const t = (v, lang) => {
  if (v == null) return '';
  if (typeof v === 'string') return v;
  if (typeof v === 'object') {
    return v[lang] || v.es || v.en || v.pt || '';
  }
  return String(v);
};

const getData = () => window[BRAND.dataKey] || {};

// ---------- brand palette helpers ----------
const BRAND_PALETTES = {
  skywave: {
    light: {
      brand: '#BA0C2F',
      brandSoft: 'rgba(186,12,47,0.08)',
      signal: '#1863DC',
      signalSoft: 'rgba(24,99,220,0.10)',
      cyan: '#1863DC',
    },
  },
  viachain: {
    light: {
      brand: '#163A63',
      brandSoft: 'rgba(22,58,99,0.09)',
      signal: '#2C6FB7',
      signalSoft: 'rgba(44,111,183,0.12)',
      cyan: '#2C6FB7',
    },
  }
};

const getBrandPalette = (brand, mode) => {
  const b = String(brand || '').toLowerCase();
  return (BRAND_PALETTES[b] && BRAND_PALETTES[b][mode]) || BRAND_PALETTES.skywave.light;
};

// ---------- design tokens ----------
const _LIGHT_BASE = {
  bg: '#FFFFFF',
  bgAlt: '#F4F4F4',
  bgDeep: '#0D0A07',
  bgDeepAlt: '#1A1510',
  panel: '#FFFFFF',
  panelHi: '#F8F8F8',
  panelInv: '#1A1510',
  line: '#EBEBEB',
  lineHi: 'rgba(13,10,7,0.18)',
  lineDark: 'rgba(255,255,255,0.10)',
  lineDarkHi: 'rgba(255,255,255,0.22)',
  text: '#0D0A07',
  textMut: '#666666',
  textDim: '#999999',
  textInv: '#F7F8FA',
  textInvMut: 'rgba(247,248,250,0.62)',
  textInvDim: 'rgba(247,248,250,0.38)',
  accent: '#0D0A07',
  accentInv: '#F7F8FA',
  solar: '#E8B259',
  ok: '#1FA971',
  red: '#BA0C2F',
  redSoft: 'rgba(186,12,47,0.08)',
};

const _LIGHT = Object.assign({}, _LIGHT_BASE, getBrandPalette(BRAND.brand, 'light'));
const COLORS = _LIGHT;

// ---------- typography ----------
const FONT_SANS = "'Roboto', sans-serif";
const FONT_DISPLAY = "'Roboto', sans-serif";
const FONT_SERIF = "'Roboto', sans-serif";
const FONT_MONO = "'Roboto Mono', monospace";

// backward compatibility aliases
const FONTSANS = FONT_SANS;
const FONTDISPLAY = FONT_DISPLAY;
const FONTSERIF = FONT_SERIF;
const FONTMONO = FONT_MONO;

// ---------- utility primitives ----------
const Eyebrow = ({ children, color, dark = false, style }) => {
  const c = color || (dark ? COLORS.textInvMut : COLORS.textMut);
  return (
    <div style={{
      fontFamily: FONT_MONO,
      fontSize: 11,
      letterSpacing: 2.2,
      textTransform: 'uppercase',
      color: c,
      display: 'flex',
      alignItems: 'center',
      gap: 10,
      ...style
    }}>
      <span style={{ width: 22, height: 1, background: c, display: 'inline-block', opacity: 0.5 }} />
      {children}
    </div>
  );
};

const SectionNum = ({ n, label, dark = false }) => (
  <div style={{
    display: 'flex',
    alignItems: 'baseline',
    gap: 14,
    color: dark ? COLORS.textInvMut : COLORS.textMut,
    fontFamily: FONT_MONO,
    fontSize: 11,
    letterSpacing: 2,
    textTransform: 'uppercase'
  }}>
    <span style={{ color: dark ? COLORS.textInv : COLORS.text, fontWeight: 600 }}>/ {n}</span>
    <span>{label}</span>
  </div>
);

// ---------- logo ----------
const BrandLogo = () => (
  <img
    src={BRAND.logoSrc}
    alt={String(BRAND.brand || 'brand')}
    style={{
      height: BRAND.logoHeight || 40,
      maxHeight: 48,
      display: 'block',
      objectFit: 'contain'
    }}
  />
);

const SkywaveLogo = BrandLogo;

// ---------- header ----------
const Header = ({ scene, setScene }) => {
  const { lang, setLang } = useLang();
  const D = getData();
  const items = Object.entries(D.nav || {}).map(([k, label]) => ({ k, label }));
  const headerBg = 'rgba(255,255,255,0.84)';

  return (
    <header style={{
      position: 'fixed',
      top: 0,
      left: 0,
      right: 0,
      zIndex: 100,
      padding: '16px 40px',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      gap: 20,
      background: headerBg,
      backdropFilter: 'blur(18px)',
      WebkitBackdropFilter: 'blur(18px)',
      borderBottom: `1px solid ${COLORS.line}`,
    }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 28, minWidth: 0 }}>
        <button
          onClick={() => setScene(items[0]?.k || 'overview')}
          style={{ background: 'none', border: 'none', cursor: 'pointer', padding: 0, flexShrink: 0 }}
        >
          <BrandLogo />
        </button>

        <div style={{ height: 14, width: 1, background: COLORS.line, flexShrink: 0 }} />

        <span style={{
          fontFamily: FONT_MONO,
          fontSize: 10,
          letterSpacing: 2,
          color: COLORS.textMut,
          textTransform: 'uppercase',
          whiteSpace: 'nowrap'
        }}>
          {BRAND.portfolioLabel}
        </span>
      </div>

      <nav style={{ display: 'flex', gap: 2, flexWrap: 'wrap', justifyContent: 'center' }}>
        {items.map((it) => (
          <button
            key={it.k}
            onClick={() => setScene(it.k)}
            style={{
              background: 'none',
              border: 'none',
              cursor: 'pointer',
              padding: '8px 14px',
              fontFamily: FONT_SANS,
              fontSize: 13,
              letterSpacing: 0.1,
              fontWeight: 500,
              color: scene === it.k ? COLORS.text : COLORS.textMut,
              borderBottom: `1.5px solid ${scene === it.k ? COLORS.brand : 'transparent'}`,
              transition: 'all .2s',
            }}
          >
            {t(it.label, lang)}
          </button>
        ))}
      </nav>

      <div style={{
        display: 'flex',
        alignItems: 'center',
        gap: 8,
        fontFamily: FONT_MONO,
        fontSize: 11,
        flexShrink: 0
      }}>
        {(BRAND.langs || ['es', 'en', 'pt']).map((l) => (
          <button
            key={l}
            onClick={() => setLang(l)}
            style={{
              background: lang === l ? COLORS.text : 'transparent',
              color: lang === l ? COLORS.bg : COLORS.textMut,
              border: `1px solid ${lang === l ? COLORS.text : COLORS.line}`,
              padding: '4px 9px',
              cursor: 'pointer',
              textTransform: 'uppercase',
              letterSpacing: 1,
              fontFamily: FONT_MONO,
              fontSize: 10,
              fontWeight: 600,
              borderRadius: 3,
            }}
          >
            {l}
          </button>
        ))}

        <div style={{ width: 1, height: 14, background: COLORS.line, margin: '0 2px' }} />

        <a
          href="/"
          title="Home"
          style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            width: 28,
            height: 28,
            borderRadius: 4,
            border: `1px solid ${COLORS.line}`,
            color: COLORS.textMut,
            textDecoration: 'none',
            fontSize: 14,
            lineHeight: 1,
            transition: 'border-color .15s, color .15s',
          }}
          onMouseEnter={(e) => {
            e.currentTarget.style.borderColor = COLORS.brand;
            e.currentTarget.style.color = COLORS.brand;
          }}
          onMouseLeave={(e) => {
            e.currentTarget.style.borderColor = COLORS.line;
            e.currentTarget.style.color = COLORS.textMut;
          }}
        >
          ⌂
        </a>
      </div>
    </header>
  );
};

Object.assign(window, {
  useState,
  useEffect,
  useMemo,
  useRef,
  LangCtx,
  ThemeCtx,
  useLang,
  useTheme,
  t,
  getData,
  getBrandPalette,
  BRAND,
  COLORS,
  _LIGHT,
  FONT_SANS,
  FONT_DISPLAY,
  FONT_SERIF,
  FONT_MONO,
  FONTSANS,
  FONTDISPLAY,
  FONTSERIF,
  FONTMONO,
  Eyebrow,
  SectionNum,
  BrandLogo,
  SkywaveLogo,
  Header
});
