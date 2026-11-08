import { useCallback, useEffect, useRef, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Modal,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { recordDonation, searchReturningDonors } from '../api/client';
import EmptyState from '../components/EmptyState';
import { capitalizeWords, composeMbdDate } from '../collectionForm';

export default function ReturningDonorScreen() {
  const [query, setQuery] = useState('');
  const [donors, setDonors] = useState([]);
  const [selectedDonor, setSelectedDonor] = useState(null);
  const [mbdType, setMbdType] = useState('MBD');
  const [mbdOn, setMbdOn] = useState(new Date().toISOString().slice(0, 10));
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [message, setMessage] = useState('');
  const [searched, setSearched] = useState(false);
  const requestId = useRef(0);

  const search = useCallback(async (searchTerm = query) => {
    const term = searchTerm.trim();
    const currentRequest = ++requestId.current;
    if (!term) {
      setDonors([]);
      setLoading(false);
      setSearched(false);
      return;
    }

    setLoading(true);
    setError('');
    setMessage('');
    setSearched(true);

    try {
      const data = await searchReturningDonors(term);
      if (currentRequest === requestId.current) {
        setDonors(data.donors ?? []);
      }
    } catch (requestError) {
      if (currentRequest === requestId.current) {
        setDonors([]);
        setError(requestError.message);
      }
    } finally {
      if (currentRequest === requestId.current) {
        setLoading(false);
      }
    }
  }, [query]);

  useEffect(() => {
    const term = query.trim();
    if (!term) {
      requestId.current += 1;
      setDonors([]);
      setLoading(false);
      setSearched(false);
      return undefined;
    }

    setDonors([]);
    setLoading(true);
    setSearched(true);
    setSelectedDonor(null);
    const timeout = setTimeout(() => search(term), 250);

    return () => {
      clearTimeout(timeout);
      requestId.current += 1;
    };
  }, [query, search]);

  const saveDonation = async () => {
    if (!selectedDonor) {
      return;
    }

    setSaving(true);
    setError('');
    setMessage('');

    try {
      await recordDonation(selectedDonor.id, {
        mbd_type: mbdType,
        mbd_on: mbdOn,
        mbd_date: composeMbdDate(mbdType, mbdOn),
      });
      setMessage(`Donation recorded for ${selectedDonor.name}.`);
      setSelectedDonor(null);
      setMbdType('MBD');
      setMbdOn(new Date().toISOString().slice(0, 10));
      setQuery('');
      setDonors([]);
      setSearched(false);
    } catch (requestError) {
      setError(requestError.message);
    } finally {
      setSaving(false);
    }
  };

  return (
    <View style={styles.root}>
      <Text style={styles.hint}>Find an existing donor to record another collection.</Text>
      <View style={styles.searchRow}>
        <TextInput
          style={styles.searchInput}
          placeholder="Search donor name"
          placeholderTextColor="#94a3b8"
          value={query}
          autoCapitalize="words"
          onChangeText={(value) => {
            setQuery(capitalizeWords(value));
            setSelectedDonor(null);
            setError('');
            setMessage('');
          }}
          onSubmitEditing={() => search()}
          returnKeyType="search"
          accessibilityLabel="Search returning donors"
        />
        <Pressable style={styles.searchButton} onPress={() => search()} accessibilityRole="button">
          <Text style={styles.searchButtonIcon}>⌕</Text>
        </Pressable>
      </View>

      {error ? <Text style={styles.error}>{error}</Text> : null}
      {message ? <Text style={styles.success}>{message}</Text> : null}
      {loading ? <ActivityIndicator style={styles.loader} color="#990000" /> : null}

      {selectedDonor ? (
        <ScrollView style={styles.formScroll} keyboardShouldPersistTaps="handled">
          <View style={styles.donationCard}>
            <View style={styles.selectedHeader}>
              <View style={styles.selectedCopy}>
                <Text style={styles.selectedName}>{selectedDonor.name}</Text>
                <Text style={styles.selectedMeta}>
                  {selectedDonor.blood_type} · {selectedDonor.donations} previous donations
                </Text>
              </View>
              <Pressable
                style={styles.changeButton}
                onPress={() => setSelectedDonor(null)}
                accessibilityRole="button"
                accessibilityLabel="Choose a different donor"
              >
                <Text style={styles.changeButtonText}>Change</Text>
              </Pressable>
            </View>
            <Text style={styles.fieldLabel}>MBD / Date</Text>
            <View style={styles.mbdRow}>
              <View style={styles.mbdType}>
                <SelectField value={mbdType} options={['MBD', 'WI']} onChange={setMbdType} placeholder="MBD or WI" />
              </View>
              <TextInput
                style={[styles.input, styles.mbdDate]}
                value={mbdOn}
                onChangeText={setMbdOn}
                placeholder="YYYY-MM-DD"
                keyboardType="numbers-and-punctuation"
                accessibilityLabel="MBD date"
              />
            </View>
            <Pressable
              style={[styles.saveButton, saving && styles.disabledButton]}
              onPress={saveDonation}
              disabled={saving}
              accessibilityRole="button"
            >
              <Text style={styles.saveButtonText}>{saving ? 'Saving…' : 'Record donation'}</Text>
            </Pressable>
          </View>
        </ScrollView>
      ) : (
        <>
          {donors.length > 0 ? (
            <Text style={styles.resultsHeading}>
              {donors.length} matching donor{donors.length === 1 ? '' : 's'}
            </Text>
          ) : null}
          <FlatList
            data={donors}
            keyExtractor={(item) => String(item.id)}
            keyboardShouldPersistTaps="handled"
            ItemSeparatorComponent={() => <View style={styles.separator} />}
            renderItem={({ item }) => (
              <Pressable
                style={styles.donorRow}
                onPress={() => setSelectedDonor(item)}
                accessibilityRole="button"
              >
                <View style={styles.bloodBadge}>
                  <Text style={styles.bloodBadgeText}>{item.blood_type || '—'}</Text>
                </View>
                <View style={styles.donorCopy}>
                  <Text style={styles.donorName} numberOfLines={1}>{item.name}</Text>
                  <Text style={styles.donorMeta}>{item.donations} previous donations</Text>
                </View>
                <Text style={styles.chevron}>›</Text>
              </Pressable>
            )}
            ListEmptyComponent={
              searched && !loading
                ? <EmptyState title="No returning donors found" message="Check the name and try again." />
                : !loading
                  ? <EmptyState title="Search for a donor" message="Type one letter to see matching donors." />
                  : null
            }
            contentContainerStyle={donors.length === 0 ? styles.emptyList : styles.listContent}
          />
        </>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff', padding: 16 },
  hint: { color: '#64748b', fontSize: 13, lineHeight: 18, marginBottom: 12 },
  searchRow: { flexDirection: 'row', gap: 8, alignItems: 'center' },
  searchInput: {
    flex: 1,
    minWidth: 0,
    minHeight: 46,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 11,
    paddingHorizontal: 12,
    fontSize: 15,
    color: '#1e293b',
  },
  searchButton: {
    height: 46,
    width: 48,
    borderRadius: 11,
    backgroundColor: '#990000',
    alignItems: 'center',
    justifyContent: 'center',
  },
  searchButtonIcon: { color: '#fff', fontSize: 25, lineHeight: 28 },
  loader: { marginTop: 16 },
  error: { color: '#dc2626', fontSize: 13, marginTop: 10 },
  success: { color: '#15803d', fontSize: 13, fontWeight: '600', marginTop: 10 },
  listContent: { paddingTop: 14, paddingBottom: 20 },
  resultsHeading: { color: '#64748b', fontSize: 12, fontWeight: '700', marginTop: 14 },
  emptyList: { flexGrow: 1, justifyContent: 'center', paddingBottom: 32 },
  separator: { height: 8 },
  donorRow: {
    minHeight: 62,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    paddingHorizontal: 11,
    paddingVertical: 9,
    borderWidth: 1,
    borderColor: '#edf1f5',
    borderRadius: 12,
    backgroundColor: '#fff',
  },
  bloodBadge: {
    minWidth: 40,
    height: 38,
    paddingHorizontal: 5,
    borderRadius: 11,
    backgroundColor: '#fff0f0',
    alignItems: 'center',
    justifyContent: 'center',
  },
  bloodBadgeText: { color: '#990000', fontWeight: '800', fontSize: 12 },
  donorCopy: { flex: 1, minWidth: 0 },
  donorName: { color: '#1e293b', fontSize: 14, fontWeight: '700' },
  donorMeta: { color: '#64748b', fontSize: 11, marginTop: 3 },
  chevron: { color: '#94a3b8', fontSize: 25 },
  donationCard: {
    marginTop: 16,
    padding: 14,
    borderWidth: 1,
    borderColor: '#edf1f5',
    borderRadius: 14,
    backgroundColor: '#fff',
  },
  selectedHeader: { flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 8 },
  selectedCopy: { flex: 1, minWidth: 0 },
  selectedName: { color: '#1e293b', fontSize: 16, fontWeight: '800' },
  selectedMeta: { color: '#64748b', fontSize: 12, marginTop: 4 },
  changeButton: {
    minHeight: 34,
    justifyContent: 'center',
    paddingHorizontal: 10,
    borderRadius: 9,
    backgroundColor: '#f1f5f9',
  },
  changeButtonText: { color: '#475569', fontWeight: '700', fontSize: 12 },
  formScroll: { flex: 1 },
  mbdRow: { flexDirection: 'row', gap: 8, alignItems: 'center' },
  mbdType: { width: 108 },
  mbdDate: { flex: 1, minWidth: 0 },
  fieldLabel: { color: '#475569', fontWeight: '700', fontSize: 12, marginTop: 10, marginBottom: 5 },
  dropdown: {
    minHeight: 44,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 10,
    paddingHorizontal: 11,
    backgroundColor: '#fff',
  },
  dropdownDisabled: { backgroundColor: '#f8fafc', opacity: 0.7 },
  dropdownValue: { color: '#1e293b', fontSize: 14, flex: 1 },
  dropdownPlaceholder: { color: '#94a3b8', fontSize: 14, flex: 1 },
  dropdownArrow: { color: '#64748b', fontSize: 16 },
  modalBackdrop: { flex: 1, justifyContent: 'flex-end', backgroundColor: 'rgba(15, 23, 42, 0.45)' },
  pickerCard: { maxHeight: '70%', backgroundColor: '#fff', borderTopLeftRadius: 16, borderTopRightRadius: 16, padding: 16 },
  pickerTitle: { color: '#1e293b', fontWeight: '800', fontSize: 16, marginBottom: 8 },
  pickerOptions: { flexGrow: 0 },
  pickerOption: { minHeight: 44, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  pickerOptionSelected: {},
  pickerOptionText: { color: '#1e293b', fontSize: 15 },
  pickerOptionTextSelected: { color: '#990000', fontWeight: '800' },
  pickerCheck: { color: '#990000', fontWeight: '800' },
  input: {
    minHeight: 44,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 10,
    paddingHorizontal: 11,
    fontSize: 14,
    color: '#1e293b',
  },
  saveButton: {
    minHeight: 46,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#990000',
    borderRadius: 11,
    marginTop: 16,
  },
  disabledButton: { opacity: 0.65 },
  saveButtonText: { color: '#fff', fontWeight: '700', fontSize: 14 },
});

function SelectField({ value, options, onChange, placeholder, disabled = false }) {
  const [visible, setVisible] = useState(false);
  const isDisabled = disabled || options.length === 0;

  return (
    <>
      <Pressable
        style={[styles.dropdown, isDisabled && styles.dropdownDisabled]}
        onPress={() => setVisible(true)}
        disabled={isDisabled}
        accessibilityRole="button"
        accessibilityLabel={value || placeholder}
      >
        <Text style={value ? styles.dropdownValue : styles.dropdownPlaceholder} numberOfLines={1}>
          {value || placeholder}
        </Text>
        <Text style={styles.dropdownArrow}>⌄</Text>
      </Pressable>
      <Modal visible={visible} transparent animationType="fade" onRequestClose={() => setVisible(false)}>
        <Pressable style={styles.modalBackdrop} onPress={() => setVisible(false)}>
          <Pressable style={styles.pickerCard} onPress={(event) => event.stopPropagation()}>
            <Text style={styles.pickerTitle}>{placeholder}</Text>
            <ScrollView style={styles.pickerOptions}>
              {options.map((option) => (
                <Pressable
                  key={option}
                  style={styles.pickerOption}
                  onPress={() => {
                    onChange(option);
                    setVisible(false);
                  }}
                  accessibilityRole="button"
                >
                  <Text style={[styles.pickerOptionText, option === value && styles.pickerOptionTextSelected]}>
                    {option}
                  </Text>
                  {option === value ? <Text style={styles.pickerCheck}>✓</Text> : null}
                </Pressable>
              ))}
            </ScrollView>
          </Pressable>
        </Pressable>
      </Modal>
    </>
  );
}
