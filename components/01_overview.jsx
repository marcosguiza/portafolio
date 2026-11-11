const OverviewScene = ({ setScene, setActiveProduct }) => {
  const { lang } = useLang();
  const D = getData();

  const products = Array.isArray(D.products) ? D.products : [];
  const featured = products.slice(0, Math.min(products.length, 8));

  const heroStatsRaw = pickFirstArray([
    D.stats,
    D.heroStats,
    D.kpis,
    D.metrics,
    D.overviewStats
  ]);
  const heroStats = normalizeStats(heroStatsRaw).filter(hasRenderableStat).slice(0, 4);

  const [cardsPerView, setCardsPerView] = useState(getCardsPerView());
  const [visionIndex, setVisionIndex] = useState(0);

  useEffect(() => {
    const onResize = () => setCardsPerView(getCardsPerView());
    window.addEventListener('resize', onResize);
    return () => window.removeEventListener('resize', onResize);
  }, []);

  useEffect(() => {
    const maxIndex = Math.max(0, featured.length - cardsPerView);
    if (visionIndex > maxIndex) setVisionIndex(maxIndex);
  }, [cardsPerView, featured.length, visionIndex]);

  const maxIndex = Math.max(0, featured.length - cardsPerView);

  const openProduct = (productId) => {
    if (typeof setActiveProduct === 'function') setActiveProduct(productId);
    if (typeof setScene === 'function') setScene('showroom');
  };

  const goVision = (dir) => {
    setVisionIndex((prev) => {
      if (dir === 'next') return Math.min(prev + 1, maxIndex);
      return Math.max(prev - 1, 0);
    });
  };

  const accentGlow = BRAND.brand === 'viachain'
    ? 'radial-gradient(circle at 50% 50%, rgba(22,58,99,0.16) 0%, rgba(22,58,99,0.05) 40%, rgba(22,58,99,0) 72%)'
    : 'radial-gradient(circle at 50% 50%, rgba(186,12,47,0.14) 0%, rgba(186,12,47,0.05) 40%, rgba(186,12,47,0) 72%)';

  return (
    <div style={{ background: COLORS.bg, minHeight: '100vh' }}>
      <HeroSection
        D={D}
        lang={lang}
        heroStats={heroStats}
        setScene={setScene}
      />

      <section style={{
        position: 'relative',
        padding: '72px 40px 64px',
        background: COLORS.bg,
        overflow: 'hidden'
      }}>
        <div style={{
          position: 'absolute',
          inset: 'auto 0 0 0',
          height: 220,
          background: accentGlow,
          pointerEvents: 'none'
        }} />

        <div style={{ position: 'relative', maxWidth: 1320, margin: '0 auto' }}>
          <div style={{
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'end',
            gap: 24,
            marginBottom: 30,
            flexWrap: 'wrap'
          }}>
            <div style={{ maxWidth: 760 }}>
              <SectionNum
                n="02"
                label={t({ es: 'Vision de portafolio', en: 'Portfolio vision', pt: 'Visão do portfólio' }, lang)}
              />
              <h2 style={{
                fontFamily: FONT_SANS,
                fontSize: 'clamp(28px, 4vw, 48px)',
                lineHeight: 1.05,
                letterSpacing: -1.2,
                color: COLORS.text,
                margin: '18px 0 12px'
              }}>
                {t(
                  D.overviewVisionTitle || {
                    es: 'Terminales y dispositivos presentados como sistema',
                    en: 'Terminals and devices presented as one system',
                    pt: 'Terminais e dispositivos apresentados como um sistema'
                  },
                  lang
                )}
              </h2>
              <p style={{
                fontFamily: FONT_SANS,
                fontSize: 16,
                lineHeight: 1.7,
                color: COLORS.textMut,
                maxWidth: 760
              }}>
                {t(
                  D.overviewVisionText || {
                    es: 'Explora la línea disponible desde una vista general. Cada equipo puede abrirse luego en showroom para revisar capacidades, recursos y material asociado.',
                    en: 'Explore the available line from a high-level view. Each product can later be opened in the showroom to review capabilities, assets and related material.',
                    pt: 'Explore a linha disponível a partir de uma visão geral. Cada produto pode ser aberto depois no showroom para revisar capacidades, recursos e materiais relacionados.'
                  },
                  lang
                )}
              </p>
            </div>

            {featured.length > cardsPerView && (
              <div style={{ display: 'flex', gap: 10 }}>
                <button onClick={() => goVision('prev')} disabled={visionIndex === 0} style={visionNavButton(visionIndex === 0)}>←</button>
                <button onClick={() => goVision('next')} disabled={visionIndex >= maxIndex} style={visionNavButton(visionIndex >= maxIndex)}>→</button>
              </div>
            )}
          </div>

          <div style={{ overflow: 'hidden' }}>
            <div style={{
              display: 'grid',
              gridAutoFlow: 'column',
              gridAutoColumns:
                cardsPerView === 3 ? 'calc((100% - 40px) / 3)' :
                cardsPerView === 2 ? 'calc((100% - 20px) / 2)' :
                '100%',
              gap: 20,
              transform:
                cardsPerView === 3
                  ? `translateX(calc(-${visionIndex} * ((100% - 40px) / 3 + 20px)))`
                  : cardsPerView === 2
                  ? `translateX(calc(-${visionIndex} * ((100% - 20px) / 2 + 20px)))`
                  : `translateX(calc(-${visionIndex} * (100% + 20px)))`,
              transition: 'transform .35s cubic-bezier(.2,.9,.3,1)',
              willChange: 'transform'
            }}>
              {featured.map((product, idx) => (
                <ProductVisionCard
                  key={product.id || idx}
                  product={product}
                  idx={idx}
                  lang={lang}
                  onOpen={() => openProduct(product.id)}
                />
              ))}
            </div>
          </div>
        </div>
      </section>
    </div>
  );
};

const HeroSection = ({ D, lang, heroStats, setScene }) => {
  const hero = D.hero || {};

  const eyebrow = t(
    D.heroEyebrow || hero.eyebrow || {
      es: 'Portafolio LATAM',
      en: 'LATAM portfolio',
      pt: 'Portfólio LATAM'
    },
    lang
  );

  const title = t(
    D.heroTitle || D.title || hero.title || {
      es: 'Portafolio de soluciones para operaciones críticas',
      en: 'Solutions portfolio for critical operations',
      pt: 'Portfólio de soluções para operações críticas'
    },
    lang
  );

  const text = t(
    D.heroText || D.subtitle || hero.sub || {
      es: 'Una presentación estructurada del portafolio para exploración comercial, demostración técnica y revisión de material por producto.',
      en: 'A structured portfolio presentation for commercial exploration, technical demonstration and product-level material review.',
      pt: 'Uma apresentação estruturada do portfólio para exploração comercial, demonstração técnica e revisão de materiais por produto.'
    },
    lang
  );

  return (
    <section style={{
      position: 'relative',
      overflow: 'hidden',
      padding: '128px 40px 72px',
      background: COLORS.bg
    }}>
      <div style={{
        position: 'absolute',
        inset: 0,
        background: BRAND.brand === 'viachain'
          ? 'linear-gradient(180deg, rgba(22,58,99,0.07) 0%, rgba(22,58,99,0) 55%)'
          : 'linear-gradient(180deg, rgba(186,12,47,0.06) 0%, rgba(186,12,47,0) 55%)',
        pointerEvents: 'none'
      }} />

      <div style={{
        position: 'relative',
        maxWidth: 1320,
        margin: '0 auto',
        display: 'grid',
        gridTemplateColumns: heroStats.length ? 'minmax(0, 1.2fr) minmax(320px, 0.8fr)' : '1fr',
        gap: 28,
        alignItems: 'stretch'
      }}>
        <div style={{
          background: COLORS.panel,
          border: `1px solid ${COLORS.line}`,
          borderRadius: 24,
          padding: '34px 34px 30px',
          minHeight: 380,
          display: 'flex',
          flexDirection: 'column',
          justifyContent: 'space-between',
          boxShadow: BRAND.theme === 'dark' ? 'none' : '0 24px 60px rgba(13,10,7,0.05)'
        }}>
          <div>
            <Eyebrow>{eyebrow}</Eyebrow>

            <h1 style={{
              fontFamily: FONT_SANS,
              fontSize: 'clamp(34px, 6vw, 76px)',
              lineHeight: 0.98,
              letterSpacing: -2.4,
              color: COLORS.text,
              margin: '22px 0 18px',
              maxWidth: 820
            }}>
              {Array.isArray(title)
                ? title.map((line, idx) => <React.Fragment key={idx}>{idx > 0 && <br />}{line}</React.Fragment>)
                : title}
            </h1>

            <p style={{
              fontFamily: FONT_SANS,
              fontSize: 17,
              lineHeight: 1.75,
              color: COLORS.textMut,
              maxWidth: 760
            }}>
              {text}
            </p>
          </div>

          <div style={{
            display: 'flex',
            gap: 12,
            flexWrap: 'wrap',
            marginTop: 26
          }}>
            <button
              onClick={() => setScene('showroom')}
              style={{
                background: COLORS.brand,
                color: COLORS.textInv,
                border: `1px solid ${COLORS.brand}`,
                padding: '13px 18px',
                borderRadius: 10,
                cursor: 'pointer',
                fontFamily: FONT_SANS,
                fontSize: 14,
                fontWeight: 500
              }}
            >
              {t(hero.scroll || { es: 'Abrir showroom', en: 'Open showroom', pt: 'Abrir showroom' }, lang)}
            </button>

            <button
              onClick={() => setScene('accessories')}
              style={{
                background: 'transparent',
                color: COLORS.text,
                border: `1px solid ${COLORS.line}`,
                padding: '13px 18px',
                borderRadius: 10,
                cursor: 'pointer',
                fontFamily: FONT_SANS,
                fontSize: 14,
                fontWeight: 500
              }}
            >
              {t({ es: 'Accesorios', en: 'Accessories', pt: 'Acessórios' }, lang)}
            </button>
          </div>
        </div>

        {!!heroStats.length && (
          <div style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(2, minmax(0, 1fr))',
            gap: 16
          }}>
            {heroStats.map((item, idx) => (
              <div key={idx} style={{
                background: BRAND.theme === 'dark' ? COLORS.panel : COLORS.bgAlt,
                border: `1px solid ${COLORS.line}`,
                borderRadius: 20,
                padding: '24px 22px',
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'space-between',
                minHeight: 150
              }}>
                <div style={{
                  fontFamily: FONT_MONO,
                  fontSize: 10,
                  letterSpacing: 1.8,
                  textTransform: 'uppercase',
                  color: COLORS.brand
                }}>
                  {t(item.label, lang)}
                </div>

                <div style={{
                  fontFamily: FONT_SANS,
                  fontSize: 'clamp(26px, 3.2vw, 40px)',
                  lineHeight: 1,
                  letterSpacing: -1.2,
                  color: COLORS.text,
                  margin: '14px 0 10px'
                }}>
                  {t(item.value, lang)}
                </div>

                <div style={{
                  fontFamily: FONT_SANS,
                  fontSize: 13.5,
                  lineHeight: 1.6,
                  color: COLORS.textMut
                }}>
                  {t(item.text, lang)}
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </section>
  );
};

const ProductVisionCard = ({ product, idx, lang, onOpen }) => {
  const media = product.image || product.img || product.thumb || product.photo || '';
  const category = t(product.category || product.type || '', lang);
  const title = t(product.name || product.title || '', lang);
  const text = t(
    product.cardText || product.summary || product.hero || product.description || '',
    lang
  );

  return (
    <button
      onClick={onOpen}
      style={{
        background: COLORS.panel,
        border: `1px solid ${COLORS.line}`,
        borderRadius: 20,
        overflow: 'hidden',
        cursor: 'pointer',
        textAlign: 'left',
        padding: 0,
        minHeight: 430,
        display: 'flex',
        flexDirection: 'column',
        transition: 'transform .2s ease, box-shadow .2s ease, border-color .2s ease',
        boxShadow: BRAND.theme === 'dark' ? 'none' : '0 18px 40px rgba(13,10,7,0.05)'
      }}
      onMouseEnter={(e) => {
        e.currentTarget.style.transform = 'translateY(-2px)';
        e.currentTarget.style.borderColor = COLORS.brand;
      }}
      onMouseLeave={(e) => {
        e.currentTarget.style.transform = 'translateY(0)';
        e.currentTarget.style.borderColor = COLORS.line;
      }}
    >
      <div style={{
        position: 'relative',
        aspectRatio: '16 / 10',
        background: BRAND.theme === 'dark'
          ? 'linear-gradient(180deg, rgba(255,255,255,0.02) 0%, rgba(255,255,255,0.00) 100%)'
          : 'linear-gradient(180deg, rgba(13,10,7,0.02) 0%, rgba(13,10,7,0.00) 100%)',
        borderBottom: `1px solid ${COLORS.line}`,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: 24
      }}>
        <div style={{
          position: 'absolute',
          top: 16,
          left: 16,
          fontFamily: FONT_MONO,
          fontSize: 10,
          letterSpacing: 1.6,
          textTransform: 'uppercase',
          color: COLORS.brand
        }}>
          {String(idx + 1).padStart(2, '0')}
        </div>

        {media ? (
          <img
            src={media}
            alt={title}
            style={{
              maxWidth: '100%',
              maxHeight: '100%',
              objectFit: 'contain',
              display: 'block'
            }}
          />
        ) : (
          <div style={{
            width: '100%',
            height: '100%',
            borderRadius: 16,
            border: `1px dashed ${COLORS.line}`,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: COLORS.textDim,
            fontFamily: FONT_MONO,
            fontSize: 11,
            letterSpacing: 1.4,
            textTransform: 'uppercase'
          }}>
            No media
          </div>
        )}
      </div>

      <div style={{
        padding: '20px 20px 18px',
        display: 'flex',
        flexDirection: 'column',
        gap: 10,
        flex: 1
      }}>
        <div style={{
          fontFamily: FONT_MONO,
          fontSize: 10,
          letterSpacing: 1.5,
          textTransform: 'uppercase',
          color: COLORS.textMut
        }}>
          {category}
        </div>

        <div style={{
          fontFamily: FONT_SANS,
          fontSize: 26,
          lineHeight: 1.05,
          letterSpacing: -0.8,
          color: COLORS.text
        }}>
          {title}
        </div>

        <div style={{
          fontFamily: FONT_SANS,
          fontSize: 14,
          lineHeight: 1.7,
          color: COLORS.textMut,
          flex: 1
        }}>
          {text}
        </div>

        <div style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          paddingTop: 6,
          borderTop: `1px solid ${COLORS.line}`
        }}>
          <span style={{
            fontFamily: FONT_MONO,
            fontSize: 10,
            letterSpacing: 1.5,
            textTransform: 'uppercase',
            color: COLORS.brand
          }}>
            {t({ es: 'Abrir detalle', en: 'Open detail', pt: 'Abrir detalhe' }, lang)}
          </span>
          <span style={{ fontFamily: FONT_MONO, fontSize: 12, color: COLORS.brand }}>→</span>
        </div>
      </div>
    </button>
  );
};

function getCardsPerView() {
  const w = typeof window !== 'undefined' ? window.innerWidth : 1400;
  if (w < 760) return 1;
  if (w < 1100) return 2;
  return 3;
}

function pickFirstArray(list) {
  for (const item of list) {
    if (Array.isArray(item)) return item;
  }
  return [];
}

function normalizeStats(items) {
  return (items || []).map((item) => ({
    label: item?.label || item?.title || item?.name || item?.kpi || '',
    value: item?.value || item?.metric || item?.number || item?.stat || '',
    text: item?.text || item?.description || item?.copy || item?.desc || item?.subtitle || ''
  }));
}

function hasRenderableStat(item) {
  return String(item?.label || '').trim() && String(item?.value || '').trim();
}

const visionNavButton = (disabled) => ({
  width: 42,
  height: 42,
  borderRadius: 10,
  border: `1px solid ${disabled ? COLORS.line : COLORS.brand}`,
  background: disabled ? 'transparent' : COLORS.panel,
  color: disabled ? COLORS.textDim : COLORS.brand,
  cursor: disabled ? 'default' : 'pointer',
  fontFamily: FONT_MONO,
  fontSize: 16,
  lineHeight: 1,
  transition: 'all .2s ease',
  opacity: disabled ? 0.45 : 1
});

window.OverviewScene = OverviewScene;
