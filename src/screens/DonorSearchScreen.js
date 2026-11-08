import { useCallback, useEffect, useRef, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Modal,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import {
  deleteAllDonors,
  searchDonors,
  searchDonorsByLocation,
} from '../api/client';
import EmptyState from '../components/EmptyState';
import { colors, layout } from '../theme/layout';
import { capitalizeWords } from '../collectionForm';

const CURRENT_YEAR = String(new Date().getFullYear());

export default function DonorSearchScreen({ navigation }) {
  const [mode, setMode] = useState('name');
  const [query, setQuery] = useState('');
  const [location, setLocation] = useState('');
  const [year, setYear] = useState(CURRENT_YEAR);
  const [results, setResults] = useState([]);
  const [total, setTotal] = useState(null);
  const [loading, setLoading] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const [error, setError] = useState('');
  const [message, setMessage] = useState('');
  const [searched, setSearched] = useState(false);
  const [confirmDeleteAll, setConfirmDeleteAll] = useState(false);
  const [showAddMenu, setShowAddMenu] = useState(false);
  const [showYearPicker, setShowYearPicker] = useState(false);
  const requestId = useRef(0);
  const runSearchRef = useRef(null);
  const skipInitialSearchEffect = useRef(true);

  const runSearch = useCallback(async () => {
    const currentRequest = ++requestId.current;
    setError('');
    setMessage('');
    setLoading(true);
    setSearched(true);

    try {
      const data = mode === 'name'
        ? await searchDonors(query.trim())
        : await searchDonorsByLocation(location.trim(), year.trim());

      if (currentRequest === requestId.current) {
        setResults(data.donors ?? []);
        setTotal(data.total ?? 0);
      }
    } catch (requestError) {
      if (currentRequest === requestId.current) {
        setError(requestError.message);
        setResults([]);
        setTotal(0);
      }
    } finally {
      if (currentRequest === requestId.current) {
        setLoading(false);
      }
    }
  }, [location, mode, query, year]);

  runSearchRef.current = runSearch;

  useEffect(() => {
    if (skipInitialSearchEffect.current) {
      skipInitialSearchEffect.current = false;
      return undefined;
    }

    const timeout = setTimeout(() => {
      runSearchRef.current?.();
    }, 350);

    return () => clearTimeout(timeout);
  }, [location, mode, query, year]);

  useFocusEffect(
    useCallback(() => {
      runSearchRef.current?.();
    }, []),
  );

  const selectMode = (nextMode) => {
    if (mode === nextMode) {
      return;
    }

    requestId.current += 1;
    setMode(nextMode);
    setQuery('');
    setLocation('');
    setYear(CURRENT_YEAR);
    setResults([]);
    setTotal(null);
    setSearched(false);
    setLoading(false);
    setError('');
    setMessage('');
  };

  const confirmDelete = async () => {
    setDeleting(true);
    setError('');
    setMessage('');

    try {
      const response = await deleteAllDonors();
      setResults([]);
      setTotal(0);
      setSearched(true);
      setMessage(response.message);
    } catch (requestError) {
      setError(requestError.message);
      setConfirmDeleteAll(false);
    } finally {
      setDeleting(false);
    }
  };

  return (
    <View style={layout.screen}>
      <View style={styles.titleRow}>
        <View style={styles.headingIcon}>
          <Text style={styles.headingIconText}>♥</Text>
        </View>
        <View style={styles.titleCopy}>
          <Text style={layout.screenTitle}>Blood Collection</Text>
          <Text style={styles.subtitle}>Search and manage donor records</Text>
        </View>
      </View>

      <View style={styles.modeTabs}>
        <ModeTab
          active={mode === 'name'}
          icon="⌕"
          label="Search by Name"
          onPress={() => selectMode('name')}
        />
        <ModeTab
          active={mode === 'location'}
          icon="⌖"
          label="Location & Year"
          onPress={() => selectMode('location')}
        />
      </View>

      <View style={styles.searchPanel}>
        {mode === 'name' ? (
          <View style={styles.searchRow}>
            <TextInput
              style={[layout.input, styles.nameInput]}
              placeholder="Search donors"
              placeholderTextColor="#94a3b8"
              value={query}
              onChangeText={(value) => setQuery(capitalizeWords(value))}
              autoCapitalize="words"
              onSubmitEditing={runSearch}
              returnKeyType="search"
              accessibilityLabel="Search donors by name or contact"
            />
            <AddDonorButton onPress={() => setShowAddMenu(true)} />
          </View>
        ) : (
          <View style={styles.locationSearchRow}>
            <TextInput
              style={[layout.input, styles.locationInput]}
              placeholder="Location"
              placeholderTextColor="#94a3b8"
              value={location}
              onChangeText={(value) => setLocation(capitalizeWords(value))}
              autoCapitalize="words"
              accessibilityLabel="Search location"
            />
            <Pressable
              style={styles.yearPicker}
              onPress={() => setShowYearPicker(true)}
              accessibilityRole="button"
              accessibilityLabel={`Collection year ${year}. Select year`}
            >
              <Text style={styles.yearPickerText}>{year}</Text>
              <Text style={styles.yearPickerArrow}>⌄</Text>
            </Pressable>
            <AddDonorButton onPress={() => setShowAddMenu(true)} />
          </View>
        )}
      </View>

      <Modal
        visible={showYearPicker}
        transparent
        animationType="fade"
        onRequestClose={() => setShowYearPicker(false)}
      >
        <Pressable style={styles.yearModalBackdrop} onPress={() => setShowYearPicker(false)}>
          <View style={styles.yearModal}>
            <Text style={styles.yearModalTitle}>Select collection year</Text>
            <FlatList
              data={Array.from(
                { length: Math.max(1, new Date().getFullYear() - 2014) },
                (_, index) => String(new Date().getFullYear() - index),
              )}
              keyExtractor={(item) => item}
              style={styles.yearOptions}
              renderItem={({ item }) => {
                const selected = year === item;
                return (
                  <Pressable
                    style={[styles.yearOption, selected && styles.yearOptionSelected]}
                    onPress={() => {
                      setYear(item);
                      setShowYearPicker(false);
                    }}
                    accessibilityRole="button"
                    accessibilityState={{ selected }}
                  >
                    <Text style={[styles.yearOptionText, selected && styles.yearOptionTextSelected]}>
                      {item}
                    </Text>
                    {selected ? <Text style={styles.yearCheck}>✓</Text> : null}
                  </Pressable>
                );
              }}
            />
          </View>
        </Pressable>
      </Modal>

      <View style={styles.resultsHeader}>
        <Text style={styles.count}>
          {total === null ? 'Donor records' : `${total} ${total === 1 ? 'donor' : 'donors'} found`}
        </Text>
        {total > 0 ? (
          <Pressable
            style={({ pressed }) => [
              styles.deleteAllButton,
              (loading || deleting) && styles.deleteAllDisabled,
              pressed && styles.buttonPressed,
            ]}
            disabled={loading || deleting}
            onPress={() => setConfirmDeleteAll(true)}
            accessibilityRole="button"
            accessibilityLabel="Delete all donor records"
          >
            {deleting
              ? <ActivityIndicator size="small" color={colors.danger} />
              : <Text style={styles.deleteAllText}>Delete All</Text>}
          </Pressable>
        ) : null}
      </View>

      {loading ? <ActivityIndicator style={styles.loader} color={colors.primary} /> : null}
      {error ? <Text style={styles.error}>{error}</Text> : null}
      {message ? <Text style={styles.success}>{message}</Text> : null}

      <FlatList
        data={results}
        keyExtractor={(item) => String(item.id)}
        contentContainerStyle={results.length === 0 ? styles.emptyList : styles.listContent}
        keyboardShouldPersistTaps="handled"
        refreshing={loading}
        onRefresh={runSearch}
        ItemSeparatorComponent={() => <View style={styles.separator} />}
        renderItem={({ item }) => (
          <Pressable
            style={styles.donorRow}
            onPress={() => navigation.navigate('DonorDetail', { donorId: item.id, donorName: item.name })}
            accessibilityRole="button"
          >
            <Text style={styles.name} numberOfLines={1}>{item.name}</Text>
            <Text style={styles.rowChevron}>›</Text>
          </Pressable>
        )}
        ListEmptyComponent={
          !loading && searched
            ? <EmptyState title="No donors found" message="Try a different name, location, or year." />
            : !loading
              ? <EmptyState title="Search donor records" message="Choose a search mode and enter your search." />
              : null
        }
      />

      <Modal
        visible={showAddMenu}
        transparent
        animationType="fade"
        onRequestClose={() => setShowAddMenu(false)}
      >
        <View style={styles.modalBackdrop}>
          <Pressable
            style={StyleSheet.absoluteFill}
            onPress={() => setShowAddMenu(false)}
            accessibilityLabel="Close add donor menu"
          />
          <View style={styles.addMenuCard}>
            <Text style={styles.addMenuTitle}>Add donor</Text>
            <Pressable
              style={styles.addMenuOption}
              onPress={() => {
                setShowAddMenu(false);
                navigation.navigate('RegisterDonor');
              }}
              accessibilityRole="button"
            >
              <View style={styles.addMenuIconWrap}>
                <Text style={styles.addMenuIcon}>＋</Text>
              </View>
              <View style={styles.addMenuCopy}>
                <Text style={styles.addMenuOptionTitle}>First-time donor</Text>
                <Text style={styles.addMenuOptionHint}>Register a new donor profile</Text>
              </View>
              <Text style={styles.rowChevron}>›</Text>
            </Pressable>
            <Pressable
              style={styles.addMenuOption}
              onPress={() => {
                setShowAddMenu(false);
                navigation.navigate('ReturningDonor');
              }}
              accessibilityRole="button"
            >
              <View style={styles.addMenuIconWrap}>
                <Text style={styles.addMenuIcon}>↻</Text>
              </View>
              <View style={styles.addMenuCopy}>
                <Text style={styles.addMenuOptionTitle}>Returning donor</Text>
                <Text style={styles.addMenuOptionHint}>Find a donor and record another donation</Text>
              </View>
              <Text style={styles.rowChevron}>›</Text>
            </Pressable>
            <Pressable
              style={styles.addMenuCancel}
              onPress={() => setShowAddMenu(false)}
              accessibilityRole="button"
            >
              <Text style={styles.addMenuCancelText}>Cancel</Text>
            </Pressable>
          </View>
        </View>
      </Modal>

      <Modal
        visible={confirmDeleteAll}
        transparent
        animationType="fade"
        onRequestClose={() => setConfirmDeleteAll(false)}
      >
        <View style={styles.modalBackdrop}>
          <View style={styles.confirmCard}>
            <View style={styles.confirmIconWrap}>
              <Text style={styles.confirmIcon}>×</Text>
            </View>
            <Text style={styles.confirmTitle}>Delete all donors?</Text>
            <Text style={styles.confirmMessage}>
              This permanently removes all donor records and their collection history. This action cannot be undone.
            </Text>
            <View style={styles.confirmActions}>
              <Pressable
                style={styles.cancelButton}
                onPress={() => setConfirmDeleteAll(false)}
                disabled={deleting}
              >
                <Text style={styles.cancelButtonText}>Cancel</Text>
              </Pressable>
              <Pressable
                style={styles.confirmDeleteButton}
                onPress={confirmDelete}
                disabled={deleting}
              >
                <Text style={styles.confirmDeleteText}>{deleting ? 'Deleting…' : 'Delete All'}</Text>
              </Pressable>
            </View>
          </View>
        </View>
      </Modal>

    </View>
  );
}

function ModeTab({ active, icon, label, onPress }) {
  return (
    <Pressable
      style={[styles.modeTab, active && styles.modeTabActive]}
      onPress={onPress}
      accessibilityRole="button"
      accessibilityState={{ selected: active }}
    >
      <Text style={[styles.modeIcon, active && styles.modeIconActive]}>{icon}</Text>
      <Text style={[styles.modeLabel, active && styles.modeLabelActive]} numberOfLines={1}>
        {label}
      </Text>
    </Pressable>
  );
}

function AddDonorButton({ onPress }) {
  return (
    <Pressable
      style={styles.searchAddButton}
      onPress={onPress}
      accessibilityRole="button"
      accessibilityLabel="Add donor"
    >
      <Text style={styles.searchAddButtonIcon}>＋</Text>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  titleRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    gap: 10,
    marginBottom: 16,
  },
  headingIcon: {
    width: 42,
    height: 42,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#fff1f1',
  },
  headingIconText: { color: colors.primary, fontSize: 20, lineHeight: 24, fontWeight: '800' },
  titleCopy: { flex: 1, minWidth: 0 },
  subtitle: { color: colors.muted, fontSize: 12, marginTop: 3 },
  addButton: {
    width: 44,
    height: 44,
    backgroundColor: colors.primary,
    borderRadius: 22,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#4b0000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.18,
    shadowRadius: 4,
    elevation: 2,
  },
  addButtonIcon: { color: '#fff', fontSize: 28, lineHeight: 31, fontWeight: '400' },
  modeTabs: {
    flexDirection: 'row',
    backgroundColor: '#f8fafc',
    borderRadius: 13,
    padding: 4,
    borderWidth: 1,
    borderColor: '#eef2f7',
    marginBottom: 12,
  },
  modeTab: {
    flex: 1,
    minWidth: 0,
    minHeight: 42,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 5,
    paddingHorizontal: 5,
    borderRadius: 10,
  },
  modeTabActive: {
    backgroundColor: colors.primary,
    shadowColor: '#4b0000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.16,
    shadowRadius: 4,
    elevation: 2,
  },
  modeIcon: { color: colors.muted, fontSize: 19, lineHeight: 21 },
  modeIconActive: { color: '#fff' },
  modeLabel: { color: '#475569', fontSize: 12, fontWeight: '600', flexShrink: 1 },
  modeLabelActive: { color: '#fff' },
  searchPanel: {
    backgroundColor: '#fff',
    borderRadius: 13,
    padding: 12,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#edf1f5',
    shadowColor: '#0f172a',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 7,
    elevation: 2,
  },
  searchRow: { flexDirection: 'row', gap: 8, alignItems: 'center' },
  locationSearchRow: { flexDirection: 'row', gap: 7, alignItems: 'center' },
  nameInput: { flex: 1, minWidth: 0, backgroundColor: '#fff' },
  locationInput: { flex: 1, minWidth: 0, backgroundColor: '#fff' },
  yearPicker: {
    width: 86,
    minHeight: 44,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 10,
    backgroundColor: '#fff',
  },
  yearPickerText: { color: colors.text, fontSize: 13, fontWeight: '700' },
  yearPickerArrow: { color: colors.muted, fontSize: 16, lineHeight: 18 },
  searchAddButton: {
    width: 44,
    height: 44,
    borderRadius: 10,
    backgroundColor: colors.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  searchAddButtonIcon: { color: '#fff', fontSize: 27, lineHeight: 30, fontWeight: '400' },
  yearModalBackdrop: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
    backgroundColor: 'rgba(15, 23, 42, 0.42)',
  },
  yearModal: {
    width: '100%',
    maxWidth: 340,
    maxHeight: '75%',
    padding: 16,
    borderRadius: 16,
    backgroundColor: '#fff',
    elevation: 8,
  },
  yearModalTitle: { color: colors.text, fontSize: 16, fontWeight: '800', marginBottom: 10 },
  yearOptions: { flexShrink: 1 },
  yearOption: {
    minHeight: 42,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 11,
    borderRadius: 9,
  },
  yearOptionSelected: { backgroundColor: '#fff1f1' },
  yearOptionText: { color: colors.text, fontSize: 14, fontWeight: '600' },
  yearOptionTextSelected: { color: colors.primary, fontWeight: '800' },
  yearCheck: { color: colors.primary, fontWeight: '800' },
  resultsHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    minHeight: 36,
    marginBottom: 4,
  },
  count: { color: '#475569', fontWeight: '700', fontSize: 13, flex: 1 },
  deleteAllButton: {
    minHeight: 32,
    justifyContent: 'center',
    paddingHorizontal: 7,
  },
  deleteAllText: { color: colors.danger, fontSize: 11, fontWeight: '700' },
  deleteAllDisabled: { opacity: 0.45 },
  buttonPressed: { opacity: 0.7 },
  loader: { marginVertical: 10 },
  error: { color: colors.danger, marginBottom: 8, fontSize: 13 },
  success: { color: '#15803d', marginBottom: 8, fontSize: 13, fontWeight: '600' },
  listContent: { paddingVertical: 4, paddingBottom: 16 },
  emptyList: { flexGrow: 1, justifyContent: 'center', paddingBottom: 24 },
  separator: { height: 8 },
  donorRow: {
    minHeight: 58,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    backgroundColor: '#fff',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#edf1f5',
    paddingHorizontal: 14,
    paddingVertical: 9,
    shadowColor: '#0f172a',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.04,
    shadowRadius: 5,
    elevation: 1,
  },
  name: { color: '#bf1e35', fontSize: 12, fontWeight: '700' },
  rowChevron: { color: '#94a3b8', fontSize: 23, lineHeight: 26, paddingLeft: 2 },
  addMenuCard: {
    width: '100%',
    maxWidth: 380,
    backgroundColor: '#fff',
    borderRadius: 18,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.2,
    shadowRadius: 18,
    elevation: 8,
  },
  addMenuTitle: { color: colors.text, fontSize: 17, fontWeight: '800', marginBottom: 8 },
  addMenuOption: {
    minHeight: 64,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 11,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
    paddingVertical: 9,
  },
  addMenuIconWrap: {
    width: 38,
    height: 38,
    borderRadius: 12,
    backgroundColor: '#fff0f0',
    alignItems: 'center',
    justifyContent: 'center',
  },
  addMenuIcon: { color: colors.primary, fontSize: 22, lineHeight: 25, fontWeight: '600' },
  addMenuCopy: { flex: 1, minWidth: 0 },
  addMenuOptionTitle: { color: colors.text, fontSize: 14, fontWeight: '700' },
  addMenuOptionHint: { color: colors.muted, fontSize: 11, marginTop: 3 },
  addMenuCancel: { alignItems: 'center', justifyContent: 'center', minHeight: 42, marginTop: 5 },
  addMenuCancelText: { color: colors.muted, fontWeight: '700', fontSize: 13 },
  modalBackdrop: {
    flex: 1,
    backgroundColor: 'rgba(15, 23, 42, 0.48)',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
  },
  confirmCard: {
    width: '100%',
    maxWidth: 380,
    backgroundColor: '#fff',
    borderRadius: 18,
    padding: 20,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.2,
    shadowRadius: 18,
    elevation: 8,
  },
  confirmIconWrap: {
    width: 46,
    height: 46,
    borderRadius: 23,
    backgroundColor: '#fff1f2',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  confirmIcon: { color: colors.danger, fontSize: 30, lineHeight: 34, fontWeight: '400' },
  confirmTitle: { color: colors.text, fontSize: 18, fontWeight: '800', textAlign: 'center' },
  confirmMessage: {
    color: colors.muted,
    fontSize: 13,
    lineHeight: 19,
    textAlign: 'center',
    marginTop: 8,
  },
  confirmActions: { flexDirection: 'row', gap: 9, width: '100%', marginTop: 20 },
  cancelButton: {
    flex: 1,
    minHeight: 44,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  cancelButtonText: { color: '#475569', fontSize: 13, fontWeight: '700' },
  confirmDeleteButton: {
    flex: 1,
    minHeight: 44,
    borderRadius: 10,
    backgroundColor: colors.danger,
    alignItems: 'center',
    justifyContent: 'center',
  },
  confirmDeleteText: { color: '#fff', fontSize: 13, fontWeight: '700' },
});
