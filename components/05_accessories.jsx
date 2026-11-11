const AccessoriesScene = () => {
  const { lang } = useLang();
  const D = getData();
  const accessories = Array.isArray(D.accessories) ? D.accessories : [];
  const ownAccessories = accessories.filter(a => !a.thirdParty);
  const thirdPartyAccessories = accessories.filter(a => a.thirdParty);
  const [selected, setSelected] = useState(null);
  const [docOpen, setDocOpen] = useState(false);
  const [activeDoc, setActiveDoc] = useState(null);

  const acc = selected ? accessories.find((a) => a.id === selected) : null;

  const labels = {
    compatible:   { es: 'Compatible con', en: 'Compatible with', pt: 'Compatível com' },
    dimensions:   { es: 'Dimensiones', en: 'Dimensions', pt: 'Dimensões' },
    environment:  { es: 'Ambiente', en: 'Environment', pt: 'Ambiente' },
    connectivity: { es: 'Conectividad', en: 'Connectivity', pt: 'Conectividade' },
    documents:    { es: 'Documentos', en: 'Documents', pt: 'Documentos' },
    datasheet:    { es: 'Ficha técnica', en: 'Datasheet', pt: 'Ficha técnica' },
    hardware:     { es: 'Guía de hardware', en: 'Hardware guide', pt: 'Guia de hardware' },
    install:      { es: 'Guía de instalación', en: 'Installation guide', pt: 'Guia de instalação' },
    noDocs:       { es: 'No hay documentos disponibles para este accesorio.', en: 'No documents are available for this accessory.', pt: 'Não há documentos disponíveis para este acessório.' },
    back:         { es: '← Accesorios', en: '← Accessories', pt: '← Acessórios' },
    title:        { es: 'Accesorios', en: 'Accessories', pt: 'Acessórios' },
    sub:          D.accessoriesIntro || { es: 'Accesorios certificados para el portafolio.', en: 'Portfolio-certified accessories.', pt: 'Acessórios certificados para o portfólio.' },
    selected:         { es: 'Accesorio seleccionado', en: 'Selected accessory', pt: 'Acessório selecionado' },
    openPdf:          { es: 'Abrir PDF', en: 'Open PDF', pt: 'Abrir PDF' },
    noImage:          { es: 'Sin imagen', en: 'No image', pt: 'Sem imagem' },
    thirdPartyBadge:  { es: 'Tercero', en: 'Third-party', pt: 'Terceiro' },
    thirdPartyTitle:  { es: 'Sensores integrados de terceros', en: 'Integrated third-party sensors', pt: 'Sensores integrados de terceiros' },
    thirdPartySub:    { es: 'Sensores de fabricantes especializados certificados para integrarse de forma nativa con los dispositivos del portafolio.', en: 'Sensors from specialized manufacturers certified to integrate natively with portfolio devices.', pt: 'Sensores de fabricantes especializados certificados para integração nativa com os dispositivos do portfólio.' }
  };

  const openDocument = (doc) => {
    setActiveDoc(doc);
    setDocOpen(true);
  };

  if (acc) {
    const docs = getAccessoryDocs(D, acc, lang, labels);

    return (
      <div className="scene-fade" style={{ background: COLORS.bg, minHeight: '100vh', paddingTop: 100, paddingBottom: 88 }}>
        <div style={{ maxWidth: 1220, margin: '0 auto', padding: '0 40px' }}>
          <button
            onClick={() => {
              setSelected(null);
              setDocOpen(false);
              setActiveDoc(null);
            }}
            style={{
              background: 'none',
              border: 'none',
              cursor: 'pointer',
              fontFamily: FONT_MONO,
              fontSize: 11,
              color: COLORS.brand,
              letterSpacing: 1.2,
              marginBottom: 28,
              padding: 0
            }}
          >
            {t(labels.back, lang)}
          </button>

          <div style={{
            display: 'grid',
            gridTemplateColumns: 'minmax(320px, 460px) minmax(0, 1fr)',
            gap: 56,
            alignItems: 'start'
          }}>
            <div>
              <div style={{
                background: `radial-gradient(ellipse at 50% 60%, ${COLORS.panelHi}, ${COLORS.panel} 72%)`,
                border: `1px solid ${COLORS.line}`,
                borderRadius: 22,
                padding: 32,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                minHeight: 380
              }}>
                {acc.img ? (
                  <img
                    src={acc.img}
                    alt={acc.name}
                    style={{ maxWidth: '100%', maxHeight: 320, objectFit: 'contain' }}
                  />
                ) : (
                  <div style={{ fontFamily: FONT_MONO, fontSize: 11, color: COLORS.textDim }}>
                    {t(labels.noImage, lang)}
                  </div>
                )}
              </div>
            </div>

            <div>
              <Eyebrow color={COLORS.brand}>
                {t(labels.selected, lang)}
              </Eyebrow>

              <div style={{
                fontFamily: FONT_MONO,
                fontSize: 10,
                letterSpacing: 1.6,
                color: COLORS.brand,
                textTransform: 'uppercase',
                marginTop: 14,
                marginBottom: 8,
                display: 'flex',
                alignItems: 'center',
                gap: 10
              }}>
                {resolveText(acc.category, lang)}
                {acc.thirdParty && (
                  <span style={{
                    background: COLORS.brandSoft,
                    color: COLORS.brand,
                    borderRadius: 4,
                    padding: '2px 8px',
                    fontSize: 9,
                    letterSpacing: 0.8,
                    textTransform: 'uppercase'
                  }}>
                    {t(labels.thirdPartyBadge, lang)}
                  </span>
                )}
              </div>

              <h1 style={{
                fontFamily: FONT_SANS,
                fontSize: 'clamp(34px, 5vw, 58px)',
                fontWeight: 400,
                color: COLORS.text,
                margin: '0 0 10px',
                lineHeight: 0.98,
                letterSpacing: -1.4
              }}>
                {acc.name}
              </h1>

              {resolveText(acc.hero, lang) && (
                <p style={{
                  fontFamily: FONT_SANS,
                  fontSize: 18,
                  color: COLORS.textMut,
                  fontStyle: 'italic',
                  margin: '0 0 18px',
                  lineHeight: 1.55
                }}>
                  {resolveText(acc.hero, lang)}
                </p>
              )}

              <p style={{
                fontFamily: FONT_SANS,
                fontSize: 15,
                color: COLORS.text,
                lineHeight: 1.75,
                margin: '0 0 28px',
                maxWidth: 760
              }}>
                {resolveText(acc.description, lang)}
              </p>

              <div style={{
                borderTop: `1px solid ${COLORS.line}`,
                borderBottom: `1px solid ${COLORS.line}`,
                padding: '20px 0',
                display: 'flex',
                flexDirection: 'column',
                gap: 12,
                marginBottom: 28
              }}>
                {Array.isArray(acc.compatible) && acc.compatible.length > 0 && (
                  <SpecRow label={t(labels.compatible, lang)}>
                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                      {acc.compatible.map((c) => (
                        <span
                          key={c}
                          style={{
                            fontFamily: FONT_MONO,
                            fontSize: 10,
                            padding: '5px 9px',
                            background: COLORS.brandSoft,
                            color: COLORS.brand,
                            borderRadius: 6,
                            letterSpacing: 0.3
                          }}
                        >
                          {c}
                        </span>
                      ))}
                    </div>
                  </SpecRow>
                )}

                {acc.dims && acc.dims !== '—' && (
                  <SpecRow label={t(labels.dimensions, lang)}>
                    {resolveText(acc.dims, lang)}
                  </SpecRow>
                )}

                {acc.env && acc.env !== '—' && (
                  <SpecRow label={t(labels.environment, lang)}>
                    {resolveText(acc.env, lang)}
                  </SpecRow>
                )}

                {Array.isArray(acc.ports) && acc.ports.length > 0 && (
                  <SpecRow label={t(labels.connectivity, lang)}>
                    {acc.ports.map((x) => resolveText(x, lang)).join(' · ')}
                  </SpecRow>
                )}
              </div>

              <div>
                <div style={{
                  fontFamily: FONT_MONO,
                  fontSize: 10,
                  letterSpacing: 1.5,
                  textTransform: 'uppercase',
                  color: COLORS.textMut,
                  marginBottom: 14
                }}>
                  {t(labels.documents, lang)}
                </div>

                {docs.length === 0 ? (
                  <div style={{
                    border: `1px dashed ${COLORS.line}`,
                    background: COLORS.bgAlt,
                    color: COLORS.textMut,
                    padding: 18,
                    borderRadius: 12,
                    fontFamily: FONT_SANS,
                    fontSize: 14,
                    lineHeight: 1.6
                  }}>
                    {t(labels.noDocs, lang)}
                  </div>
                ) : (
                  <div style={{ display: 'grid', gap: 10 }}>
                    {docs.map((doc, i) => (
                      <button
                        key={`${doc.type}-${doc.label}-${i}`}
                        onClick={() => openDocument(doc)}
                        style={{
                          display: 'grid',
                          gridTemplateColumns: '44px 1fr auto',
                          alignItems: 'center',
                          gap: 14,
                          width: '100%',
                          textAlign: 'left',
                          background: COLORS.panel,
                          border: `1px solid ${COLORS.line}`,
                          borderRadius: 12,
                          padding: '12px 14px',
                          cursor: 'pointer',
                          transition: 'border-color .2s, transform .2s'
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
                          width: 44,
                          height: 44,
                          borderRadius: 10,
                          background: COLORS.brandSoft,
                          color: COLORS.brand,
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'center',
                          fontFamily: FONT_MONO,
                          fontSize: 16
                        }}>
                          PDF
                        </div>

                        <div style={{ minWidth: 0 }}>
                          <div style={{
                            fontFamily: FONT_SANS,
                            fontSize: 14,
                            color: COLORS.text,
                            fontWeight: 500,
                            lineHeight: 1.35
                          }}>
                            {doc.title}
                          </div>

                          <div style={{
                            fontFamily: FONT_MONO,
                            fontSize: 9,
                            color: COLORS.textMut,
                            letterSpacing: 0.6,
                            textTransform: 'uppercase',
                            marginTop: 4,
                            overflow: 'hidden',
                            textOverflow: 'ellipsis',
                            whiteSpace: 'nowrap'
                          }}>
                            {doc.label}
                          </div>
                        </div>

                        <div style={{
                          fontFamily: FONT_MONO,
                          fontSize: 10,
                          color: COLORS.textMut,
                          letterSpacing: 1
                        }}>
                          {t(labels.openPdf, lang)} →
                        </div>
                      </button>
                    ))}
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>

        {docOpen && activeDoc?.url && (
          <PdfModal
            url={activeDoc.url}
            title={activeDoc.label}
            onClose={() => {
              setDocOpen(false);
              setActiveDoc(null);
            }}
          />
        )}
      </div>
    );
  }

  return (
    <div style={{ background: COLORS.bg, minHeight: '100vh', paddingTop: 110, paddingBottom: 90 }}>
      <div style={{ maxWidth: 1220, margin: '0 auto', padding: '0 40px' }}>
        <SectionNum
          n="04"
          label={t((D.nav && D.nav.accessories) || labels.title, lang)}
        />

        <h2 style={{
          fontFamily: FONT_SANS,
          fontSize: 'clamp(38px, 5vw, 70px)',
          fontWeight: 400,
          color: COLORS.text,
          margin: '0 0 14px',
          lineHeight: 0.98,
          letterSpacing: -1.6
        }}>
          {t(labels.title, lang)}
        </h2>

        <p style={{
          fontFamily: FONT_SANS,
          fontSize: 16,
          color: COLORS.textMut,
          maxWidth: 720,
          lineHeight: 1.7,
          margin: '0 0 42px'
        }}>
          {t(labels.sub, lang)}
        </p>

        {ownAccessories.length > 0 && (
          <div style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(auto-fill, minmax(250px, 1fr))',
            gap: 18
          }}>
            {ownAccessories.map((acc) => (
              <AccessoryCard
                key={acc.id}
                acc={acc}
                lang={lang}
                onClick={() => setSelected(acc.id)}
              />
            ))}
          </div>
        )}

        {thirdPartyAccessories.length > 0 && (
          <div style={{ marginTop: ownAccessories.length > 0 ? 56 : 0 }}>
            <div style={{
              borderTop: ownAccessories.length > 0 ? `1px solid ${COLORS.line}` : 'none',
              paddingTop: ownAccessories.length > 0 ? 40 : 0,
              marginBottom: 28
            }}>
              <h3 style={{
                fontFamily: FONT_SANS,
                fontSize: 'clamp(22px, 3vw, 34px)',
                fontWeight: 400,
                color: COLORS.text,
                margin: '0 0 10px',
                letterSpacing: -0.8,
                lineHeight: 1.05
              }}>
                {t(labels.thirdPartyTitle, lang)}
              </h3>
              <p style={{
                fontFamily: FONT_SANS,
                fontSize: 15,
                color: COLORS.textMut,
                maxWidth: 620,
                lineHeight: 1.65,
                margin: 0
              }}>
                {t(labels.thirdPartySub, lang)}
              </p>
            </div>
            <div style={{
              display: 'grid',
              gridTemplateColumns: 'repeat(auto-fill, minmax(250px, 1fr))',
              gap: 18
            }}>
              {thirdPartyAccessories.map((acc) => (
                <AccessoryCard
                  key={acc.id}
                  acc={acc}
                  lang={lang}
                  onClick={() => setSelected(acc.id)}
                />
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

const AccessoryCard = ({ acc, lang, onClick }) => {
  const [hovered, setHovered] = useState(false);
  const preview = resolveAccessoryLead(acc, lang);

  return (
    <button
      onClick={onClick}
      onMouseEnter={() => setHovered(true)}
      onMouseLeave={() => setHovered(false)}
      style={{
        background: COLORS.panel,
        border: `1px solid ${hovered ? COLORS.brand : COLORS.line}`,
        borderRadius: 16,
        padding: 18,
        cursor: 'pointer',
        textAlign: 'left',
        transition: 'border-color .2s, transform .2s, box-shadow .2s',
        boxShadow: hovered ? `0 10px 26px ${COLORS.brandSoft}` : 'none',
        transform: hovered ? 'translateY(-2px)' : 'translateY(0)',
        display: 'flex',
        flexDirection: 'column',
        gap: 14,
        minHeight: 100
      }}
    >
      <div style={{
        background: `radial-gradient(ellipse at 50% 60%, ${COLORS.panelHi}, ${COLORS.bgAlt} 72%)`,
        borderRadius: 12,
        height: 150,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        overflow: 'hidden',
        border: `1px solid ${COLORS.lineHi}`
      }}>
        {acc.img ? (
          <img
            src={acc.img}
            alt={acc.name}
            style={{ maxWidth: '100%', maxHeight: 122, objectFit: 'contain' }}
          />
        ) : (
          <span style={{ fontFamily: FONT_MONO, fontSize: 10, color: COLORS.textDim }}>—</span>
        )}
      </div>

      <div>
        <div style={{
          fontFamily: FONT_MONO,
          fontSize: 9,
          letterSpacing: 1.4,
          color: COLORS.brand,
          textTransform: 'uppercase',
          marginBottom: 5
        }}>
          {resolveText(acc.category, lang)}
        </div>

        <div style={{
          fontFamily: FONT_SANS,
          fontSize: 24,
          fontWeight: 400,
          color: COLORS.text,
          lineHeight: 1.02,
          letterSpacing: -0.5
        }}>
          {acc.name}
        </div>
      </div>

      {preview && (
        <div style={{
          fontFamily: FONT_SANS,
          fontSize: 13,
          color: COLORS.textMut,
          lineHeight: 1.55
        }}>
          {preview}
        </div>
      )}

      {Array.isArray(acc.compatible) && acc.compatible.length > 0 && (
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 5 }}>
          {acc.compatible.slice(0, 3).map((c) => (
            <span
              key={c}
              style={{
                fontFamily: FONT_MONO,
                fontSize: 8,
                padding: '3px 6px',
                background: COLORS.brandSoft,
                color: COLORS.brand,
                borderRadius: 4
              }}
            >
              {c}
            </span>
          ))}
          {acc.compatible.length > 3 && (
            <span style={{
              fontFamily: FONT_MONO,
              fontSize: 8,
              padding: '3px 6px',
              color: COLORS.textMut
            }}>
              +{acc.compatible.length - 3}
            </span>
          )}
        </div>
      )}
    </button>
  );
};

const SpecRow = ({ label, children }) => (
  <div style={{
    display: 'grid',
    gridTemplateColumns: '140px 1fr',
    gap: 12,
    alignItems: 'start'
  }}>
    <span style={{
      fontFamily: FONT_MONO,
      fontSize: 10,
      color: COLORS.textMut,
      letterSpacing: 0.8,
      textTransform: 'uppercase',
      paddingTop: 2
    }}>
      {label}
    </span>

    <div style={{
      fontFamily: FONT_SANS,
      fontSize: 14,
      color: COLORS.text,
      lineHeight: 1.6,
      minWidth: 0
    }}>
      {children}
    </div>
  </div>
);

function getAccessoryDocs(D, acc, lang, labels) {
  const docs = [];
  const id = acc && acc.id;

  const directEntries = normalizeDocEntries(acc && acc.datasheet, lang);
  directEntries.forEach((entry) => {
    docs.push({
      type: 'datasheet',
      title: t(labels.datasheet, lang),
      label: entry.label || fileNameFromUrl(entry.url),
      url: entry.url
    });
  });

  const datasheetEntries = normalizeDocEntries(id && D.datasheets && D.datasheets[id], lang);
  datasheetEntries.forEach((entry) => {
    docs.push({
      type: 'datasheet',
      title: t(labels.datasheet, lang),
      label: entry.label || fileNameFromUrl(entry.url),
      url: entry.url
    });
  });

  const hardwareEntries = normalizeDocEntries(id && D.hardwareGuides && D.hardwareGuides[id], lang);
  hardwareEntries.forEach((entry) => {
    docs.push({
      type: 'hardware',
      title: t(labels.hardware, lang),
      label: entry.label || fileNameFromUrl(entry.url),
      url: entry.url
    });
  });

  const installEntries = normalizeDocEntries(id && D.installGuides && D.installGuides[id], lang);
  installEntries.forEach((entry) => {
    docs.push({
      type: 'install',
      title: t(labels.install, lang),
      label: entry.label || fileNameFromUrl(entry.url),
      url: entry.url
    });
  });

  const seen = new Set();
  return docs.filter((doc) => {
    if (!doc.url) return false;
    const key = `${doc.type}|${doc.url}`;
    if (seen.has(key)) return false;
    seen.add(key);
    return true;
  });
}

function normalizeDocEntries(value, lang) {
  if (!value) return [];

  if (Array.isArray(value)) {
    return value
      .map((item) => resolveDocEntry(item, lang))
      .filter(Boolean);
  }

  const resolved = resolveDocEntry(value, lang);
  return resolved ? [resolved] : [];
}

function resolveDocEntry(value, lang) {
  if (!value) return null;

  if (typeof value === 'string') {
    return { url: value, label: fileNameFromUrl(value) };
  }

  if (Array.isArray(value)) return null;

  if (typeof value === 'object') {
    const url =
      value[lang] ||
      value.es ||
      value.en ||
      value.pt ||
      value.url;

    if (!url) return null;

    return {
      url,
      label: value.label || fileNameFromUrl(url)
    };
  }

  return null;
}

function fileNameFromUrl(url) {
  if (!url || typeof url !== 'string') return '';
  return url.split('/').pop() || url;
}

function resolveAccessoryLead(acc, lang) {
  return (
    resolveText(acc.hero, lang) ||
    resolveText(acc.role, lang) ||
    resolveText(acc.summary, lang) ||
    ''
  );
}

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

Object.assign(window, { AccessoriesScene });
