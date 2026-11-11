const CompareScene = () => {
  const { lang } = useLang();
  const D = getData();
  const products = Array.isArray(D.products) ? D.products : [];
  const [selected, setSelected] = useState(() => products.slice(0, 4).map((p) => p.id));

  useEffect(() => {
    if (!products.length) return;
    setSelected((prev) => {
      const valid = prev.filter((id) => products.some((p) => p.id === id));
      return valid.length ? valid : products.slice(0, 4).map((p) => p.id);
    });
  }, [products.length]);

  const toggle = (id) => {
    setSelected((prev) => prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]);
  };

  const cols = products.filter((p) => selected.includes(p.id));

  const compareHeaders = {
    category: t((D.compareHeaders && D.compareHeaders.category) || { es: 'Categoría', en: 'Category', pt: 'Categoria' }, lang),
    satellite: t((D.compareHeaders && D.compareHeaders.satellite) || { es: 'Satelital', en: 'Satellite', pt: 'Satelital' }, lang),
    cellular: t((D.compareHeaders && D.compareHeaders.cellular) || { es: 'Celular', en: 'Cellular', pt: 'Celular' }, lang),
    power: t((D.compareHeaders && D.compareHeaders.power) || { es: 'Energía', en: 'Power', pt: 'Energia' }, lang),
    env: t((D.compareHeaders && D.compareHeaders.env) || { es: 'Ambiente', en: 'Environment', pt: 'Ambiente' }, lang),
    prog: t((D.compareHeaders && D.compareHeaders.prog) || { es: 'Programación', en: 'Programming', pt: 'Programação' }, lang),
    backup: t((D.compareHeaders && D.compareHeaders.backup) || { es: 'Respaldo', en: 'Backup', pt: 'Backup' }, lang),
    certs: t((D.compareHeaders && D.compareHeaders.certs) || { es: 'Certificaciones', en: 'Certifications', pt: 'Certificações' }, lang),
    io: 'I/O',
    ports: t((D.compareHeaders && D.compareHeaders.ports) || { es: 'Puertos', en: 'Ports', pt: 'Portas' }, lang)
  };

  const rows = [
    { k: 'category', v: (p) => resolveText(p.category, lang) },
    { k: 'satellite', v: (p) => resolveText(p.satellite, lang) },
    { k: 'cellular', v: (p) => resolveText(p.cellular, lang) },
    { k: 'power', v: (p) => resolveText(p.power, lang) },
    { k: 'env', v: (p) => resolveText(p.env, lang) },
    { k: 'prog', v: (p) => resolveText(p.prog, lang) },
    {
      k: 'backup',
      v: (p) => p.backup
        ? t({ es: 'Sí', en: 'Yes', pt: 'Sim' }, lang)
        : '—'
    },
    { k: 'certs', v: (p) => resolveText(p.certs, lang) }
  ];

  return (
    <div style={{ minHeight: '100vh', paddingTop: 120, padding: '120px 60px 80px', background: COLORS.bg }}>
      <div style={{ maxWidth: 1600, margin: '0 auto' }}>
        <SectionNum n="04" label={t((D.nav && D.nav.compare) || { es: 'Comparador', en: 'Compare', pt: 'Comparar' }, lang)} />

        <h2 style={{ ...h2Style, marginBottom: 16 }}>
          {t({ es: 'Comparador técnico', en: 'Technical compare', pt: 'Comparador técnico' }, lang)}
        </h2>

        <p style={{
          fontFamily: FONT_SANS,
          fontSize: 16,
          color: COLORS.textMut,
          maxWidth: 760,
          margin: '0 0 40px',
          lineHeight: 1.6
        }}>
          {t({
            es: 'Selecciona los modelos que quieres revisar y compara rápidamente conectividad, energía, programación y capacidades de despliegue.',
            en: 'Select the models you want to review and compare connectivity, power, programming and deployment capabilities at a glance.',
            pt: 'Selecione os modelos que deseja revisar e compare conectividade, energia, programação e capacidades de implantação rapidamente.'
          }, lang)}
        </p>

        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8, marginBottom: 40 }}>
          {products.map((p) => {
            const active = selected.includes(p.id);
            return (
              <button
                key={p.id}
                onClick={() => toggle(p.id)}
                style={{
                  padding: '10px 16px',
                  background: active ? COLORS.text : COLORS.panel,
                  color: active ? COLORS.bg : COLORS.textMut,
                  border: `1px solid ${active ? COLORS.text : COLORS.line}`,
                  fontFamily: FONT_MONO,
                  fontSize: 11,
                  letterSpacing: 1,
                  cursor: 'pointer',
                  transition: 'all .2s',
                  whiteSpace: 'nowrap',
                  borderRadius: 8
                }}
              >
                {p.name}
              </button>
            );
          })}
        </div>

        {cols.length === 0 ? (
          <div style={{
            padding: 80,
            textAlign: 'center',
            color: COLORS.textMut,
            fontFamily: FONT_SANS,
            fontSize: 18,
            border: `1px dashed ${COLORS.line}`,
            borderRadius: 14,
            background: COLORS.bgAlt
          }}>
            {t({
              es: 'Selecciona al menos un producto para iniciar la comparación.',
              en: 'Select at least one product to start the comparison.',
              pt: 'Selecione pelo menos um produto para iniciar a comparação.'
            }, lang)}
          </div>
        ) : (
          <div style={{
            overflowX: 'auto',
            border: `1px solid ${COLORS.line}`,
            background: COLORS.bgAlt,
            borderRadius: 16
          }}>
            <div style={{
              display: 'grid',
              gridTemplateColumns: `220px repeat(${cols.length}, minmax(220px, 1fr))`,
              minWidth: 220 + cols.length * 220
            }}>
              <div style={thRow}></div>

              {cols.map((p) => (
                <div key={p.id} style={{ ...thRow, padding: 20, borderLeft: `1px solid ${COLORS.line}` }}>
                  <div style={{
                    aspectRatio: '4/3',
                    marginBottom: 12,
                    background: `radial-gradient(ellipse at 50% 60%, ${COLORS.panelHi}, ${COLORS.panel} 70%)`,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    borderRadius: 12
                  }}>
                    <img
                      src={p.img}
                      alt={p.name}
                      style={{ width: '72%', height: '72%', objectFit: 'contain' }}
                    />
                  </div>

                  <div style={{
                    fontFamily: FONT_SANS,
                    fontSize: 26,
                    color: COLORS.text,
                    letterSpacing: -0.5,
                    lineHeight: 1.05
                  }}>
                    {p.name}
                  </div>

                  <div style={{
                    fontFamily: FONT_MONO,
                    fontSize: 10,
                    letterSpacing: 1.2,
                    textTransform: 'uppercase',
                    color: COLORS.textMut,
                    marginTop: 6
                  }}>
                    {resolveText(p.category, lang)}
                  </div>
                </div>
              ))}

              {rows.map((r, i) => (
                <React.Fragment key={r.k}>
                  <div style={{
                    ...tdLabel,
                    background: i % 2 ? COLORS.panel : COLORS.bgAlt
                  }}>
                    {compareHeaders[r.k]}
                  </div>

                  {cols.map((p) => (
                    <div key={`${p.id}-${r.k}`} style={{
                      ...tdCell,
                      borderLeft: `1px solid ${COLORS.line}`,
                      background: i % 2 ? COLORS.panel : COLORS.bgAlt
                    }}>
                      {r.v(p) || '—'}
                    </div>
                  ))}
                </React.Fragment>
              ))}

              <div style={{ ...tdLabel, background: COLORS.bgAlt }}>
                {compareHeaders.io}
              </div>
              {cols.map((p) => (
                <div key={`${p.id}-io`} style={{ ...tdCell, background: COLORS.bgAlt, borderLeft: `1px solid ${COLORS.line}` }}>
                  <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                    {(Array.isArray(p.io) ? p.io : []).map((x, i) => (
                      <span key={i} style={miniTag}>{resolveText(x, lang)}</span>
                    ))}
                  </div>
                </div>
              ))}

              <div style={{ ...tdLabel, background: COLORS.panel }}>
                {compareHeaders.ports}
              </div>
              {cols.map((p) => (
                <div key={`${p.id}-ports`} style={{ ...tdCell, background: COLORS.panel, borderLeft: `1px solid ${COLORS.line}` }}>
                  <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                    {(Array.isArray(p.ports) ? p.ports : []).map((x, i) => (
                      <span key={i} style={miniTag}>{resolveText(x, lang)}</span>
                    ))}
                  </div>
                </div>
              ))}

              {cols.some(p => (p.io_16pin?.length || 0) + (p.ports_16pin?.length || 0) > 0) && (
                <React.Fragment>
                  <div style={{ ...tdLabel, background: COLORS.bgAlt, color: COLORS.brand }}>
                    + {t({ es: 'Conector 16-pin', en: '16-pin connector', pt: 'Conector 16-pin' }, lang)}
                  </div>
                  {cols.map((p) => {
                    const items = [
                      ...(Array.isArray(p.io_16pin) ? p.io_16pin : []),
                      ...(Array.isArray(p.ports_16pin) ? p.ports_16pin : []),
                    ];
                    return (
                      <div key={`${p.id}-16pin`} style={{ ...tdCell, background: COLORS.bgAlt, borderLeft: `1px solid ${COLORS.line}` }}>
                        {items.length > 0 ? (
                          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                            {items.map((x, i) => (
                              <span key={i} style={{ ...miniTag, borderStyle: 'dashed' }}>{resolveText(x, lang)}</span>
                            ))}
                          </div>
                        ) : (
                          <span style={{ color: COLORS.textDim }}>—</span>
                        )}
                      </div>
                    );
                  })}
                </React.Fragment>
              )}
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

const thRow = {
  padding: '16px 20px',
  borderBottom: `1px solid ${COLORS.lineHi}`,
  background: COLORS.panel
};

const tdLabel = {
  padding: '18px 20px',
  fontFamily: FONT_MONO,
  fontSize: 10,
  letterSpacing: 1.5,
  color: COLORS.textMut,
  textTransform: 'uppercase',
  borderBottom: `1px solid ${COLORS.line}`,
  display: 'flex',
  alignItems: 'flex-start'
};

const tdCell = {
  padding: '18px 20px',
  fontFamily: FONT_SANS,
  fontSize: 13,
  color: COLORS.text,
  borderBottom: `1px solid ${COLORS.line}`,
  lineHeight: 1.55
};

const miniTag = {
  fontFamily: FONT_MONO,
  fontSize: 10,
  padding: '4px 8px',
  background: COLORS.bg,
  border: `1px solid ${COLORS.line}`,
  color: COLORS.textMut,
  borderRadius: 6
};

const IndustriesScene = ({ setActiveProduct, setScene }) => {
  const { lang } = useLang();
  const D = getData();
  const industries = Array.isArray(D.industries) ? D.industries : [];
  const [filter, setFilter] = useState(() => industries[0]?.id || 'transportation');

  useEffect(() => {
    if (!industries.length) return;
    if (!industries.some((x) => x.id === filter)) setFilter(industries[0].id);
  }, [industries.length]);

  const ids = (D.industryMap && D.industryMap[filter]) || [];
  const products = Array.isArray(D.products) ? D.products : [];
  const matched = ids.map((id) => products.find((p) => p.id === id)).filter(Boolean);
  const activeInd = industries.find((x) => x.id === filter) || industries[0];
const accent = BRAND.brand === "viachain" ? COLORS.signal : COLORS.brand;
const accentSoft = BRAND.brand === "viachain" ? COLORS.signalSoft : COLORS.brandSoft;


  return (
    <div style={{ minHeight: '100vh', paddingTop: 120, padding: '120px 60px 80px', background: COLORS.bg }}>
      <div style={{ maxWidth: 1400, margin: '0 auto' }}>
        <SectionNum n="05" label={t((D.nav && D.nav.industries) || { es: 'Industrias', en: 'Industries', pt: 'Indústrias' }, lang)} />

        <h2 style={{ ...h2Style, marginBottom: 48 }}>
          {t({
            es: ['Un portafolio, múltiples', 'escenarios operativos.'],
            en: ['One portfolio, multiple', 'operational scenarios.'],
            pt: ['Um portfólio, múltiplos', 'cenários operacionais.']
          }, lang).map((line, i) => (
            <div key={i} style={{ color: i === 1 ? COLORS.textMut : COLORS.text, fontStyle: i === 1 ? 'italic' : 'normal' }}>
              {line}
            </div>
          ))}
        </h2>

        <div style={{
          display: 'grid',
          gridTemplateColumns: '280px 1fr',
          gap: 48
        }}>
          <div>
            <div style={{
              fontFamily: FONT_MONO,
              fontSize: 10,
              letterSpacing: 1.5,
              color: COLORS.text,
              textTransform: 'uppercase',
              marginBottom: 16
            }}>
              {t({ es: 'Filtrar por industria', en: 'Filter by industry', pt: 'Filtrar por indústria' }, lang)}
            </div>

            <div style={{
              display: 'flex',
              flexDirection: 'column',
              borderTop: `1px solid ${COLORS.line}`
            }}>
              {industries.map((ind) => {
                const active = ind.id === filter;
                const count = ((D.industryMap && D.industryMap[ind.id]) || []).length;

                return (
                  <button
                    key={ind.id}
                    onClick={() => setFilter(ind.id)}
                    style={{
                      padding: '15px 0',
                      background: 'transparent',
                      border: 'none',
                      borderBottom: `1px solid ${COLORS.line}`,
                      textAlign: 'left',
                      cursor: 'pointer',
                      display: 'flex',
                      justifyContent: 'space-between',
                      alignItems: 'center',
                      fontFamily: FONT_SANS,
                      fontSize: 14,
                      color: active ? COLORS.text : COLORS.textMut,
                      transition: 'color .15s'
                    }}
                  >
                    <span>{resolveText(ind.t, lang)}</span>
                    <span style={{
                      color: active ? COLORS.brand : COLORS.textDim,
                      fontFamily: FONT_MONO,
                      fontSize: 10,
                      letterSpacing: 1
                    }}>
                      {String(count).padStart(2, '0')} {active ? '●' : '○'}
                    </span>
                  </button>
                );
              })}
            </div>
          </div>

          <div>
            {activeInd && (
              <div style={{
                padding: '24px 28px',
                background: COLORS.panel,
                border: `1px solid ${COLORS.line}`,
                marginBottom: 32,
                borderRadius: 14
              }}>
              <Eyebrow color={accent}>{t({ es: "Industria seleccionada", en: "Selected industry", pt: "Indústria selecionada" }, lang)}</Eyebrow>


                <div style={{
                  fontFamily: FONT_SANS,
                  fontSize: 36,
                  color: COLORS.text,
                  margin: '12px 0 8px',
                  letterSpacing: -0.8,
                  lineHeight: 1.05
                }}>
                  {resolveText(activeInd.t, lang)}
                </div>

                <p style={{
                  fontFamily: FONT_SANS,
                  fontSize: 14,
                  color: COLORS.textMut,
                  margin: 0,
                  lineHeight: 1.6,
                  maxWidth: 760
                }}>
                  {resolveText(activeInd.d, lang)}
                </p>
              </div>
            )}

            <div style={{
              fontFamily: FONT_MONO,
              fontSize: 10,
              letterSpacing: 1.5,
              color: COLORS.textMut,
              textTransform: 'uppercase',
              marginBottom: 16
            }}>
              {t({ es: 'Dispositivos recomendados', en: 'Recommended devices', pt: 'Dispositivos recomendados' }, lang)} ({matched.length})
            </div>

            <div style={{
              display: 'grid',
              gridTemplateColumns: 'repeat(2, minmax(0, 1fr))',
              gap: 16
            }}>
              {matched.map((p) => (
                <button
                  key={p.id}
                  onClick={() => {
                    setActiveProduct(p.id);
                    setScene('showroom');
                  }}
                  style={{
                    background: COLORS.panel,
                    border: `1px solid ${COLORS.line}`,
                    padding: 20,
                    cursor: 'pointer',
                    textAlign: 'left',
                    display: 'flex',
                    gap: 16,
                    alignItems: 'center',
                    transition: 'border-color .2s, transform .2s',
                    borderRadius: 12
                  }}
                  onMouseEnter={(e) => {
                    e.currentTarget.style.borderColor = COLORS.brand;
                    e.currentTarget.style.transform = 'translateY(-1px)';
                  }}
                  onMouseLeave={(e) => {
                    e.currentTarget.style.borderColor = COLORS.line;
                    e.currentTarget.style.transform = 'translateY(0)';
                  }}
                >
                  <div style={{
                    width: 80,
                    height: 64,
                    flexShrink: 0,
                    background: `radial-gradient(ellipse at 50% 60%, ${COLORS.panelHi}, ${COLORS.panel} 70%)`,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    borderRadius: 10
                  }}>
                    <img src={p.img} alt={p.name} style={{ width: '80%', height: '80%', objectFit: 'contain' }} />
                  </div>

                  <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={{
                      fontFamily: FONT_SANS,
                      fontSize: 22,
                      color: COLORS.text,
                      lineHeight: 1.05
                    }}>
                      {p.name}
                    </div>

                    <div style={{
                      fontFamily: FONT_MONO,
                      fontSize: 10,
                      letterSpacing: 1,
                      color: COLORS.textMut,
                      textTransform: 'uppercase',
                      marginTop: 4
                    }}>
                      {resolveText(p.category, lang)}
                    </div>

                    <div style={{
                      fontFamily: FONT_SANS,
                      fontSize: 13,
                      color: COLORS.textMut,
                      marginTop: 8,
                      lineHeight: 1.45
                    }}>
                      {resolveBestProductLead(p, lang)}
                    </div>
                  </div>

                  <span style={{ color: COLORS.text, fontFamily: FONT_MONO }}>→</span>
                </button>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

const ArchitectureScene = () => {
  const { lang } = useLang();
  const D = getData();
  const architecture = D.architecture || {};
  const closing = D.closing || {};
  const brandLabel = String(BRAND.brand || '').toLowerCase() === 'viachain' ? 'VIACHAIN' : 'SKYWAVE';
  const contactEmail = 'guiza.marcos@orbcomm.com';

  const subject = t({
    es: `Solicitud de demo técnica · ${brandLabel}`,
    en: `Technical demo request · ${brandLabel}`,
    pt: `Solicitação de demo técnica · ${brandLabel}`
  }, lang);

  const body = t({
    es: `Hola Marcos,\n\nMe gustaría coordinar una demo técnica de la plataforma ${brandLabel}.\n\n· Empresa:\n· Cargo:\n· País:\n· Aplicación / industria:\n· Volumen estimado de activos:\n· Producto(s) de interés:\n\nGracias.`,
    en: `Hi Marcos,\n\nI'd like to schedule a technical demo of the ${brandLabel} platform.\n\n· Company:\n· Role:\n· Country:\n· Application / industry:\n· Estimated asset volume:\n· Product(s) of interest:\n\nThank you.`,
    pt: `Olá Marcos,\n\nGostaria de agendar uma demo técnica da plataforma ${brandLabel}.\n\n· Empresa:\n· Cargo:\n· País:\n· Aplicação / indústria:\n· Volume estimado de ativos:\n· Produto(s) de interesse:\n\nObrigado.`
  }, lang);

  const mailHref = `mailto:${contactEmail}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;

  return (
    <div style={{ minHeight: '100vh', paddingTop: 120, padding: '120px 60px 80px', background: COLORS.bg }}>
      <div style={{ maxWidth: 1400, margin: '0 auto' }}>
        <SectionNum n="06" label={t((D.nav && D.nav.architecture) || { es: 'Arquitectura', en: 'Architecture', pt: 'Arquitetura' }, lang)} />

        <h2 style={{ ...h2Style, marginBottom: 12 }}>
          {resolveText(
            architecture.title || { es: 'Arquitectura de la solución', en: 'Solution architecture', pt: 'Arquitetura da solução' },
            lang
          )}
        </h2>

        <p style={{
          fontFamily: FONT_SANS,
          fontSize: 20,
          color: COLORS.textMut,
          margin: '0 0 72px',
          fontWeight: 400,
          lineHeight: 1.5,
          maxWidth: 760
        }}>
          {resolveText(
            architecture.sub || { es: 'De la carga al cliente, conectado en tiempo real.', en: 'From asset to customer, connected in real time.', pt: 'Da carga ao cliente, conectado em tempo real.' },
            lang
          )}
        </p>

        <ArchitectureDiagram layers={Array.isArray(architecture.layers) ? architecture.layers : []} />

        <div style={{
          marginTop: 120,
          padding: '80px 60px',
          background: COLORS.bgAlt,
          border: `1px solid ${COLORS.line}`,
          position: 'relative',
          overflow: 'hidden',
          borderRadius: 18
        }}>
          <div style={{
            position: 'absolute',
            right: -100,
            top: -100,
            width: 400,
            height: 400,
            borderRadius: '50%',
            background: `radial-gradient(circle, ${COLORS.brandSoft}, transparent 70%)`
          }} />

          <div style={{ position: 'relative', zIndex: 2, maxWidth: 840 }}>
            <Eyebrow>
              {t({ es: 'Contacto técnico', en: 'Technical contact', pt: 'Contato técnico' }, lang)}
            </Eyebrow>

            <h3 style={{
              fontFamily: FONT_SANS,
              fontSize: 52,
              fontWeight: 400,
              color: COLORS.text,
              margin: '24px 0 20px',
              letterSpacing: -1.2,
              lineHeight: 1.08,
              textWrap: 'balance'
            }}>
              {resolveText(
                closing.title || {
                  es: `Coordina una conversación técnica sobre ${brandLabel}.`,
                  en: `Schedule a technical conversation about ${brandLabel}.`,
                  pt: `Agende uma conversa técnica sobre ${brandLabel}.`
                },
                lang
              )}
            </h3>

            <p style={{
              fontFamily: FONT_SANS,
              fontSize: 17,
              color: COLORS.textMut,
              lineHeight: 1.65,
              margin: '0 0 36px',
              maxWidth: 760
            }}>
              {resolveText(
                closing.sub || {
                  es: 'Comparte tu caso de uso, tipo de activo, cobertura requerida y volumen estimado para revisar la mejor configuración.',
                  en: 'Share your use case, asset type, required coverage and estimated volume to review the best configuration.',
                  pt: 'Compartilhe seu caso de uso, tipo de ativo, cobertura necessária e volume estimado para revisar a melhor configuração.'
                },
                lang
              )}
            </p>

            <div style={{ display: 'flex', gap: 14, alignItems: 'center', flexWrap: 'wrap' }}>
              <a
                href={mailHref}
                style={{
                  ...ctaPrimary,
                  textDecoration: 'none',
                  display: 'inline-flex',
                  alignItems: 'center',
                  gap: 8
                }}
              >
                {resolveText(
                  closing.cta || {
                    es: 'Solicitar demo técnica',
                    en: 'Request technical demo',
                    pt: 'Solicitar demo técnica'
                  },
                  lang
                )} →
              </a>

              <span style={{
                fontFamily: FONT_MONO,
                fontSize: 11,
                color: COLORS.textMut,
                letterSpacing: 1
              }}>
                {resolveText(
                  closing.contact || {
                    es: 'Contacto regional',
                    en: 'Regional contact',
                    pt: 'Contato regional'
                  },
                  lang
                )} · {contactEmail}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

const ArchitectureDiagram = ({ layers }) => {
  const { lang } = useLang();
  const [hover, setHover] = useState(null);

  if (!layers.length) return null;

  return (
    <div style={{
      display: 'grid',
      gridTemplateColumns: '1fr',
      gap: 0,
      border: `1px solid ${COLORS.line}`,
      borderRadius: 16,
      overflow: 'hidden',
      background: COLORS.panel
    }}>
      {layers.map((layer, i) => {
        const active = hover === i;

        return (
          <div
            key={layer.k || i}
            onMouseEnter={() => setHover(i)}
            onMouseLeave={() => setHover(null)}
            style={{
              display: 'grid',
              gridTemplateColumns: '80px 280px 1fr 80px',
              padding: '32px 40px',
              borderBottom: i < layers.length - 1 ? `1px solid ${COLORS.line}` : 'none',
              background: active ? COLORS.bgAlt : 'transparent',
              alignItems: 'center',
              transition: 'background .2s',
              position: 'relative'
            }}
          >
            <div style={{
              fontFamily: FONT_MONO,
              fontSize: 11,
              color: COLORS.text,
              letterSpacing: 2
            }}>
              /{layer.k || String(i + 1).padStart(2, '0')}
            </div>

            <div style={{
              fontFamily: FONT_SANS,
              fontSize: 28,
              color: COLORS.text,
              letterSpacing: -0.6,
              lineHeight: 1.05
            }}>
              {resolveText(layer.t, lang)}
            </div>

            <div style={{
              fontFamily: FONT_SANS,
              fontSize: 14,
              color: COLORS.textMut,
              lineHeight: 1.55,
              paddingRight: 24
            }}>
              {resolveText(layer.d, lang)}
            </div>

            <div style={{
              justifySelf: 'end',
              width: 40,
              height: 40,
              border: `1px solid ${active ? COLORS.brand : COLORS.line}`,
              borderRadius: '50%',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              color: active ? COLORS.brand : COLORS.textMut,
              fontFamily: FONT_MONO,
              transition: 'all .2s'
            }}>
              ↓
            </div>

            {i < layers.length - 1 && (
              <div style={{
                position: 'absolute',
                left: 96,
                bottom: -8,
                width: 1,
                height: 16,
                background: COLORS.lineHi
              }} />
            )}
          </div>
        );
      })}
    </div>
  );
};

const h2Style = {
  fontFamily: FONT_SANS,
  fontSize: 'clamp(38px, 5vw, 72px)',
  fontWeight: 400,
  color: COLORS.text,
  letterSpacing: -1.8,
  lineHeight: 0.98,
  margin: 0
};

const ctaPrimary = {
  padding: '14px 18px',
  background: COLORS.brand,
  color: COLORS.textInv,
  border: `1px solid ${COLORS.brand}`,
  fontFamily: FONT_MONO,
  fontSize: 11,
  letterSpacing: 1.1,
  textTransform: 'uppercase',
  borderRadius: 10
};

function resolveText(value, lang) {
  if (value == null) return '';
  if (typeof value === 'string') return value;
  if (typeof value === 'number') return String(value);
  if (Array.isArray(value)) return value.map((v) => resolveText(v, lang)).filter(Boolean).join(', ');
  if (typeof value === 'object') {
    if (value[lang] != null) return resolveText(value[lang], lang);
    if (value.es != null) return resolveText(value.es, lang);
    if (value.en != null) return resolveText(value.en, lang);
    if (value.pt != null) return resolveText(value.pt, lang);

    const nested = ['text', 'label', 'value', 'title', 'name', 'description', 'summary'];
    for (const key of nested) {
      if (value[key] != null) return resolveText(value[key], lang);
    }

    const first = Object.values(value).find((v) => v != null);
    return first != null ? resolveText(first, lang) : '';
  }
  return String(value);
}

function resolveBestProductLead(product, lang) {
  return (
    resolveText(product.role, lang) ||
    resolveText(product.hero, lang) ||
    resolveText(product.summary, lang) ||
    resolveText(product.descriptionShort, lang) ||
    ''
  );
}

Object.assign(window, { CompareScene, IndustriesScene, ArchitectureScene });
