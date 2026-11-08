import { useCallback, useEffect, useRef, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Modal,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import {
  deleteAllConsumptionRecords,
  fetchConsumption,
  fetchConsumptionMeta,
  fetchConsumptionPatient,
  deleteConsumptionRecord,
} from '../api/client';
import EmptyState from '../components/EmptyState';
import { colors, layout } from '../theme/layout';
import { capitalizeWords } from '../collectionForm';

const ALL_PROVINCES = 'All';
const DISPLAYED_PROVINCES = [
  'Agusan del Norte',
  'Agusan del Sur',
  'Dinagat Islands',
  'Surigao del Norte',
  'Surigao del Sur',
];

function formatVisitDate(value) {
  if (!value) {
    return 'Date not recorded';
  }

  const [year, month, day] = value.split('-').map(Number);
  if (!year || !month || !day) {
    return value;
  }

  return new Date(year, month - 1, day).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

export default function ConsumptionScreen({ navigation }) {
  const [query, setQuery] = useState('');
  const [province, setProvince] = useState('');
  const [provinces, setProvinces] = useState([]);
  const [patients, setPatients] = useState([]);
  const [componentTotals, setComponentTotals] = useState([]);
  const [totalUnits, setTotalUnits] = useState(0);
  const [matchTotal, setMatchTotal] = useState(0);
  const [recordCount, setRecordCount] = useState(0);
  const [loading, setLoading] = useState(true);
  const [deleting, setDeleting] = useState(false);
  const [error, setError] = useState('');
  const [metaError, setMetaError] = useState('');
  const [selectedPatient, setSelectedPatient] = useState('');
  const [patientVisits, setPatientVisits] = useState([]);
  const [loadingPatient, setLoadingPatient] = useState(false);
  const [patientError, setPatientError] = useState('');
  const initialFocus = useRef(true);
  const requestId = useRef(0);
  const runSearchRef = useRef(null);

  const load = useCallback(async () => {
    const currentRequest = ++requestId.current;
    setLoading(true);
    setError('');

    try {
      const data = await fetchConsumption({
        province,
        q: query.trim(),
      });

      if (currentRequest === requestId.current) {
        setPatients(data.patients ?? []);
        setComponentTotals(data.totalsByComponent ?? []);
        setTotalUnits(data.totalUnits ?? 0);
        setMatchTotal(data.matchTotal ?? 0);
        setRecordCount(data.recordCount ?? 0);
      }
    } catch (requestError) {
      if (currentRequest === requestId.current) {
        setError(requestError.message);
        setPatients([]);
        setComponentTotals([]);
        setTotalUnits(0);
        setMatchTotal(0);
        setRecordCount(0);
      }
    } finally {
      if (currentRequest === requestId.current) {
        setLoading(false);
      }
    }
  }, [province, query]);

  runSearchRef.current = load;

  useFocusEffect(
    useCallback(() => {
      if (initialFocus.current) {
        initialFocus.current = false;
        return;
      }

      runSearchRef.current?.();
    }, []),
  );

  useEffect(() => {
    let active = true;

    fetchConsumptionMeta()
      .then((data) => {
        if (active) {
          setProvinces(data.provinces ?? []);
        }
      })
      .catch((requestError) => {
        if (active) {
          setMetaError(requestError.message);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  useEffect(() => {
    const timeout = setTimeout(() => {
      load();
    }, 250);

    return () => clearTimeout(timeout);
  }, [load]);

  const openPatient = async (patient) => {
    setSelectedPatient(patient);
    setPatientVisits([]);
    setPatientError('');
    setLoadingPatient(true);

    try {
      const data = await fetchConsumptionPatient(patient, {
        province,
        q: query.trim(),
      });
      setPatientVisits(data.visits ?? []);
    } catch (requestError) {
      setPatientError(requestError.message);
    } finally {
      setLoadingPatient(false);
    }
  };

  const deleteAll = () => {
    Alert.alert(
      'Delete all consumption records?',
      'This permanently deletes every consumption record and cannot be undone.',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Delete All',
          style: 'destructive',
          onPress: async () => {
            setDeleting(true);
            setError('');
            try {
              await deleteAllConsumptionRecords();
              await load();
            } catch (requestError) {
              setError(requestError.message);
            } finally {
              setDeleting(false);
            }
          },
        },
      ],
    );
  };

  const tabs = [
    ALL_PROVINCES,
    ...DISPLAYED_PROVINCES.filter((name) => provinces.includes(name)),
  ];

  return (
    <View style={layout.screen}>
      <View style={styles.header}>
        <View style={styles.headingIcon}>
          <Text style={styles.headingIconText}>▤</Text>
        </View>
        <View style={styles.titleCopy}>
          <Text style={layout.screenTitle}>Consumption Records</Text>
          <Text style={styles.subtitle}>Search and manage patient consumption</Text>
        </View>
      </View>

      <ScrollView
        horizontal
        style={styles.tabsScroll}
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.tabs}
      >
        {tabs.map((name) => {
          const selected = (name === ALL_PROVINCES ? '' : name) === province;

          return (
            <Pressable
              key={name}
              accessibilityRole="button"
              accessibilityState={{ selected }}
              style={[
                styles.tab,
                selected && styles.tabSelected,
              ]}
              onPress={() => setProvince(name === ALL_PROVINCES ? '' : name)}
            >
              <Text
                numberOfLines={1}
                style={[styles.tabText, selected && styles.tabTextSelected]}
              >
                {name}
              </Text>
            </Pressable>
          );
        })}
      </ScrollView>

      <ScrollView
        horizontal
        style={styles.summaryScroll}
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.summaryRow}
      >
        <View style={styles.totalCard}>
          <Text style={styles.summaryLabel}>Total Units</Text>
          <Text style={styles.summaryValue}>{totalUnits}</Text>
        </View>
        {componentTotals.map((item) => (
          <View key={item.component} style={styles.componentCard}>
            <Text style={styles.summaryLabel} numberOfLines={1}>{item.component}</Text>
            <Text style={styles.summaryValue}>{item.total}</Text>
          </View>
        ))}
      </ScrollView>

      <View style={styles.searchRow}>
        <View style={styles.searchBox}>
          <Text style={styles.searchIcon}>⌕</Text>
          <TextInput
            accessibilityLabel="Search patient name"
            style={styles.searchInput}
            placeholder="Search patient name..."
            placeholderTextColor={colors.muted}
            value={query}
            onChangeText={(value) => setQuery(capitalizeWords(value))}
            onSubmitEditing={load}
            returnKeyType="search"
            autoCapitalize="words"
          />
          {query.length > 0 ? (
            <Pressable accessibilityLabel="Clear search" onPress={() => setQuery('')}>
              <Text style={styles.clearSearch}>×</Text>
            </Pressable>
          ) : null}
        </View>
        <Pressable
          accessibilityRole="button"
          accessibilityLabel="Add consumption record"
          style={styles.addButton}
          onPress={() => navigation.navigate('AddConsumption')}
        >
          <Text style={styles.addIcon}>＋</Text>
        </Pressable>
      </View>

      <View style={styles.resultRow}>
        <Text style={styles.resultCount}>{matchTotal} {matchTotal === 1 ? 'name' : 'names'} found</Text>
        {loading ? <ActivityIndicator size="small" color={colors.primary} /> : null}
      </View>

      {metaError ? <Text style={styles.error}>{metaError}</Text> : null}
      <View style={styles.listHeader}>
        <Text style={styles.listHeaderTitle}>Patient Name</Text>
        <Pressable
          accessibilityRole="button"
          accessibilityLabel="Delete all consumption records"
          disabled={deleting || recordCount === 0}
          onPress={deleteAll}
          style={({ pressed }) => [
            styles.deleteAllButton,
            (deleting || recordCount === 0) && styles.deleteAllDisabled,
            pressed && styles.buttonPressed,
          ]}
        >
          {deleting
            ? <ActivityIndicator size="small" color={colors.danger} />
            : <Text style={styles.deleteAllText}>Delete All</Text>}
        </Pressable>
      </View>

      {error ? <Text style={styles.error}>{error}</Text> : null}
      <FlatList
        style={styles.patientList}
        data={patients}
        keyExtractor={(item, index) => `${item}-${index}`}
        keyboardShouldPersistTaps="handled"
        contentContainerStyle={patients.length === 0 ? styles.emptyList : styles.listContent}
        renderItem={({ item }) => (
          <Pressable
            accessibilityRole="button"
            onPress={() => openPatient(item)}
            style={({ pressed }) => [styles.patientRow, pressed && styles.buttonPressed]}
          >
            <Text style={styles.patientName} numberOfLines={2}>{item}</Text>
            <Text style={styles.rowChevron}>›</Text>
          </Pressable>
        )}
        ListEmptyComponent={!loading && !error ? (
          <EmptyState title="No records found" message="Try another province or patient name." />
        ) : null}
      />

      <Modal
        visible={selectedPatient !== ''}
        transparent
        animationType="slide"
        onRequestClose={() => setSelectedPatient('')}
      >
        <View style={styles.modalBackdrop}>
          <View style={styles.modalCard}>
            <View style={styles.modalHeader}>
              <View style={styles.modalHeading}>
                <Text style={styles.modalEyebrow}>Patient consumption history</Text>
                <Text style={styles.modalTitle}>{selectedPatient}</Text>
                <View style={styles.modalCountBadge}>
                  <Text style={styles.modalSubtitle}>
                  {patientVisits.length} {patientVisits.length === 1 ? 'record' : 'records'}
                  </Text>
                </View>
              </View>
              <Pressable
                accessibilityLabel="Close patient history"
                onPress={() => setSelectedPatient('')}
                style={styles.closeButton}
              >
                <Text style={styles.closeButtonText}>×</Text>
              </Pressable>
            </View>
            {loadingPatient ? <ActivityIndicator color={colors.primary} style={styles.modalLoader} /> : null}
            {patientError ? <Text style={styles.error}>{patientError}</Text> : null}
            <ScrollView style={styles.visitsList} contentContainerStyle={styles.visitsContent}>
              {patientVisits.map((visit) => (
                <View key={visit.id} style={styles.visitCard}>
                  <View style={styles.visitTopRow}>
                    <Text style={styles.visitDate}>{formatVisitDate(visit.date_submitted)}</Text>
                    <View style={styles.visitActions}>
                      <Pressable
                        accessibilityRole="button"
                        onPress={() => {
                          setSelectedPatient('');
                          navigation.navigate('AddConsumption', { record: visit });
                        }}
                      >
                        <Text style={styles.visitActionText}>Edit</Text>
                      </Pressable>
                      <Pressable
                        accessibilityRole="button"
                        onPress={() => Alert.alert(
                          'Delete this record?',
                          `Delete the consumption record from ${formatVisitDate(visit.date_submitted)}?`,
                          [
                            { text: 'Cancel', style: 'cancel' },
                            {
                              text: 'Delete',
                              style: 'destructive',
                              onPress: async () => {
                                try {
                                  await deleteConsumptionRecord(visit.id);
                                  await openPatient(selectedPatient);
                                } catch (requestError) {
                                  setPatientError(requestError.message);
                                }
                              },
                            },
                          ],
                        )}
                      >
                        <Text style={styles.visitDeleteText}>Delete</Text>
                      </Pressable>
                    </View>
                  </View>
                  <View style={styles.visitDetails}>
                    <View style={styles.visitDetail}>
                      <Text style={styles.visitDetailLabel}>Age</Text>
                      <Text style={styles.visitDetailValue}>
                        {visit.age === null || visit.age === undefined ? '—' : `${visit.age} years`}
                      </Text>
                    </View>
                    <View style={styles.visitDetail}>
                      <Text style={styles.visitDetailLabel}>Sex</Text>
                      <Text style={styles.visitDetailValue}>{visit.sex || '—'}</Text>
                    </View>
                    <View style={styles.visitDetail}>
                      <Text style={styles.visitDetailLabel}>Component</Text>
                      <Text style={styles.visitDetailValue}>{visit.blood_component || '—'}</Text>
                    </View>
                    <View style={styles.visitDetail}>
                      <Text style={styles.visitDetailLabel}>Units</Text>
                      <Text style={styles.visitDetailValue}>
                        {visit.units} {visit.units === 1 ? 'unit' : 'units'}
                      </Text>
                    </View>
                    <View style={[styles.visitDetail, styles.visitDetailWide]}>
                      <Text style={styles.visitDetailLabel}>Location</Text>
                      <Text style={styles.visitDetailValue}>
                        {[visit.barangay, visit.municipality].filter(Boolean).join(', ')
                          || visit.location
                          || 'Location not recorded'}
                      </Text>
                    </View>
                  </View>
                </View>
              ))}
            </ScrollView>
          </View>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 11,
    marginBottom: 8,
  },
  headingIcon: {
    width: 42,
    height: 42,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#fff1f1',
  },
  headingIconText: { color: colors.primary, fontSize: 21, fontWeight: '800' },
  titleCopy: { minWidth: 0 },
  subtitle: { color: '#334155', fontSize: 12, marginTop: 3, fontWeight: '500' },
  addButton: {
    width: 42,
    height: 42,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 10,
    backgroundColor: colors.primary,
    shadowColor: '#4b0000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.14,
    shadowRadius: 4,
    elevation: 2,
  },
  addIcon: { color: '#fff', fontSize: 27, lineHeight: 30, fontWeight: '400' },
  tabsScroll: { flexGrow: 0, flexShrink: 0, height: 48 },
  tabs: { gap: 7, paddingBottom: 4, paddingRight: 4 },
  tab: {
    width: 112,
    height: 44,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 6,
    borderRadius: 8,
    backgroundColor: '#f8f8f8',
  },
  tabSelected: { backgroundColor: colors.primary },
  tabText: { color: '#334155', fontSize: 12, fontWeight: '700' },
  tabTextSelected: { color: '#fff' },
  summaryScroll: { flexGrow: 0, flexShrink: 0, height: 50 },
  summaryRow: { gap: 7, paddingTop: 2, paddingBottom: 4, paddingRight: 4 },
  totalCard: {
    width: 112,
    height: 44,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 0,
    paddingHorizontal: 7,
    borderRadius: 8,
    backgroundColor: '#f8f8f8',
  },
  componentCard: {
    width: 112,
    height: 44,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 0,
    paddingHorizontal: 7,
    borderRadius: 8,
    backgroundColor: '#f8f8f8',
  },
  summaryLabel: { color: '#64748b', fontSize: 10, fontWeight: '600' },
  summaryValue: { color: colors.primary, fontSize: 14, lineHeight: 17, fontWeight: '800' },
  searchRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  searchBox: {
    flex: 1,
    minHeight: 42,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 7,
    paddingHorizontal: 10,
    borderWidth: 1,
    borderColor: '#e4e8ed',
    borderRadius: 9,
    backgroundColor: '#fff',
  },
  searchIcon: { color: colors.muted, fontSize: 19, lineHeight: 21 },
  searchInput: { flex: 1, minHeight: 40, paddingVertical: 0, color: '#1f2937', fontSize: 13 },
  clearSearch: { color: colors.muted, fontSize: 22, paddingHorizontal: 2 },
  resultRow: {
    minHeight: 25,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingTop: 2,
  },
  resultCount: { color: colors.muted, fontSize: 11, fontWeight: '600' },
  listHeader: {
    minHeight: 35,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderBottomWidth: 1,
    borderBottomColor: '#e8ebef',
  },
  listHeaderTitle: { color: '#374151', fontSize: 12, fontWeight: '700' },
  deleteAllButton: { minHeight: 32, justifyContent: 'center', paddingHorizontal: 7 },
  deleteAllText: { color: colors.danger, fontSize: 11, fontWeight: '700' },
  deleteAllDisabled: { opacity: 0.45 },
  patientList: { flex: 1 },
  listContent: { paddingBottom: 18 },
  emptyList: { flexGrow: 1, justifyContent: 'center', paddingBottom: 20 },
  patientRow: {
    minHeight: 46,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    paddingHorizontal: 4,
    borderBottomWidth: 1,
    borderBottomColor: '#edf0f3',
  },
  patientName: { flex: 1, color: '#bf1e35', fontSize: 12, fontWeight: '700' },
  rowChevron: { color: '#9aa3af', fontSize: 23, lineHeight: 26, paddingHorizontal: 4 },
  error: { color: colors.danger, fontSize: 12, marginTop: 5 },
  buttonPressed: { opacity: 0.7 },
  modalBackdrop: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 12,
    paddingVertical: 24,
    backgroundColor: 'rgba(15, 23, 42, 0.45)',
  },
  modalCard: {
    width: '100%',
    maxWidth: 680,
    maxHeight: '86%',
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: '#eadfe2',
    borderTopWidth: 4,
    borderTopColor: '#800020',
    borderRadius: 14,
    backgroundColor: '#fff',
    elevation: 12,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    gap: 10,
    paddingHorizontal: 16,
    paddingTop: 13,
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#eee8e9',
  },
  modalHeading: { flex: 1 },
  modalEyebrow: {
    color: colors.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1,
    textTransform: 'uppercase',
  },
  modalTitle: { color: '#302327', fontSize: 16, lineHeight: 21, fontWeight: '800', marginTop: 3 },
  modalCountBadge: {
    alignSelf: 'flex-start',
    marginTop: 7,
    paddingHorizontal: 9,
    paddingVertical: 4,
    borderRadius: 14,
    backgroundColor: '#f8edef',
  },
  modalSubtitle: { color: '#7d2738', fontSize: 10, fontWeight: '700' },
  closeButton: {
    width: 32,
    height: 32,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#f7f4f5',
  },
  closeButtonText: { color: '#8c8587', fontSize: 22, lineHeight: 24 },
  modalLoader: { marginVertical: 18 },
  visitsList: { flexShrink: 1, backgroundColor: '#f7f5f6' },
  visitsContent: { gap: 10, padding: 12 },
  visitCard: {
    padding: 11,
    borderWidth: 1,
    borderColor: '#e9e1e3',
    borderLeftWidth: 3,
    borderLeftColor: colors.primary,
    borderRadius: 10,
    backgroundColor: '#fff',
    elevation: 1,
  },
  visitTopRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    minHeight: 25,
    marginBottom: 8,
  },
  visitActions: { flexDirection: 'row', alignItems: 'center', gap: 13 },
  visitActionText: { color: colors.primary, fontSize: 11, fontWeight: '800' },
  visitDeleteText: { color: colors.danger, fontSize: 11, fontWeight: '800' },
  visitDate: { color: '#3c242b', fontSize: 13, fontWeight: '800' },
  visitDetails: { flexDirection: 'row', flexWrap: 'wrap', gap: 6 },
  visitDetail: {
    width: '23%',
    minHeight: 48,
    flexGrow: 1,
    justifyContent: 'center',
    paddingHorizontal: 8,
    paddingVertical: 6,
    borderWidth: 1,
    borderColor: '#f0edef',
    borderRadius: 7,
    backgroundColor: '#faf9fa',
  },
  visitDetailWide: { width: '100%' },
  visitDetailLabel: {
    marginBottom: 3,
    color: '#817478',
    fontSize: 9,
    fontWeight: '800',
    letterSpacing: 0.5,
    textTransform: 'uppercase',
  },
  visitDetailValue: { color: '#393033', fontSize: 11, lineHeight: 15, fontWeight: '700' },
});
