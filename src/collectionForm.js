export function capitalizeWords(value) {
  return String(value).replace(/(^|\s|-)([a-z])/g, (lead, separator, letter) => separator + letter.toUpperCase());
}

export function titleCaseText(value) {
  return String(value ?? '').trim().replace(/\S+/g, (word) => {
    const lower = word.toLocaleLowerCase();
    return lower.charAt(0).toLocaleUpperCase() + lower.slice(1);
  });
}

export function splitMbdDate(value) {
  const text = String(value ?? '');
  const type = /\bWI\b/i.test(text) ? 'WI' : 'MBD';
  const iso = text.match(/(\d{4})-(\d{2})-(\d{2})/);
  if (iso) {
    return { type, date: `${iso[1]}-${iso[2]}-${iso[3]}` };
  }

  const monthDayYear = text.match(/(\d{1,2})\/(\d{1,2})\/(\d{4})/);
  if (!monthDayYear) {
    return { type, date: '' };
  }

  return {
    type,
    date: `${monthDayYear[3]}-${monthDayYear[1].padStart(2, '0')}-${monthDayYear[2].padStart(2, '0')}`,
  };
}

export function composeMbdDate(type, isoDate) {
  const [year, month, day] = String(isoDate ?? '').split('-');
  if (!year || !month || !day) {
    return '';
  }

  return `${type === 'WI' ? 'WI' : 'MBD'} ${month}/${day}/${year}`;
}
