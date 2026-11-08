import { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { fetchReports } from '../api/client';

const VIEWS = [
  { id: 'periodic', label: 'Periodic' },
  { id: 'demographics', label: 'Demographics' },
  { id: 'consumption', label: 'Consumption' },
];

export default function ReportsScreen() {
  const [view, setView] = useState('periodic');
  const [period, setPeriod] = useState('monthly');
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const load = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const year = String(new Date().getFullYear());
      setData(await fetchReports({ view, period, year, consumption_year: year }));
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  }, [view, period]);

  useEffect(() => {
    load();
  }, [load]);

  return (
    <ScrollView style={styles.root} contentContainerStyle={{ padding: 16 }}>
      <View style={styles.tabs}>
        {VIEWS.map((item) => (
          <Pressable
            key={item.id}
            style={[styles.tab, view === item.id && styles.tabActive]}
            onPress={() => setView(item.id)}
          >
            <Text style={view === item.id ? styles.tabTextActive : styles.tabText}>{item.label}</Text>
          </Pressable>
        ))}
      </View>
      {view === 'periodic' ? (
        <View style={styles.row}>
          {['monthly', 'quarterly', 'yearly'].map((p) => (
            <Pressable key={p} style={[styles.chip, period === p && styles.chipActive]} onPress={() => setPeriod(p)}>
              <Text style={period === p ? styles.chipTextActive : styles.chipText}>{p}</Text>
            </Pressable>
          ))}
        </View>
      ) : null}
      {loading ? <ActivityIndicator color="#990000" style={{ marginTop: 16 }} /> : null}
      {error ? <Text style={styles.error}>{error}</Text> : null}
      {data && view === 'periodic' ? (
        <>
          {(data.periodic?.rows ?? []).map((row) => (
            <View key={row.id} style={styles.card}>
              <Text style={styles.cardTitle}>{row.label}</Text>
              <Text>{row.detail}</Text>
              <Text style={styles.stat}>Donors: {row.donors} · Bags: {row.bags}</Text>
            </View>
          ))}
        </>
      ) : null}
      {data && view === 'demographics' ? (
        <>
          <Text style={styles.section}>Age groups ({data.ageReport?.totalDonors ?? 0} donors)</Text>
          {(data.ageReport?.groups ?? []).map((group) => (
            <View key={group.group} style={styles.card}>
              <Text style={styles.cardTitle}>{group.group}</Text>
              <Text>{group.count} donors · {group.bags} bags · {group.percentage}%</Text>
            </View>
          ))}
          <Text style={styles.section}>Gender</Text>
          {(data.gender?.rows ?? []).map((row) => (
            <View key={row.gender} style={styles.card}>
              <Text style={styles.cardTitle}>{row.gender}</Text>
              <Text>{row.count} ({row.percentage}%) · Top type: {row.topBloodType}</Text>
            </View>
          ))}
        </>
      ) : null}
      {data && view === 'consumption' ? (
        <>
          <Text style={styles.section}>Total units: {data.consumption?.totalUnits ?? 0}</Text>
          <Text>Provinces: {data.consumption?.provinceCount ?? 0}</Text>
          {(data.consumption?.byBloodType ?? []).map((row) => (
            <View key={row.type} style={styles.card}>
              <Text style={styles.cardTitle}>{row.type || 'Unknown'}</Text>
              <Text>{row.total} units</Text>
            </View>
          ))}
        </>
      ) : null}
      <Pressable style={styles.refresh} onPress={load}>
        <Text style={styles.refreshText}>Refresh</Text>
      </Pressable>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff' },
  tabs: { flexDirection: 'row', gap: 8, marginBottom: 12 },
  tab: { flex: 1, padding: 10, borderRadius: 8, backgroundColor: '#f1f5f9', alignItems: 'center' },
  tabActive: { backgroundColor: '#990000' },
  tabText: { color: '#334155', fontWeight: '600', fontSize: 12 },
  tabTextActive: { color: '#fff', fontWeight: '700', fontSize: 12 },
  row: { flexDirection: 'row', flexWrap: 'wrap', gap: 6, marginBottom: 12 },
  chip: { borderWidth: 1, borderColor: '#e2e8f0', borderRadius: 8, paddingHorizontal: 10, paddingVertical: 6 },
  chipActive: { backgroundColor: '#990000', borderColor: '#990000' },
  chipText: { color: '#334155' },
  chipTextActive: { color: '#fff', fontWeight: '700' },
  section: { fontWeight: '800', color: '#990000', marginTop: 12, marginBottom: 8 },
  card: { padding: 12, borderWidth: 1, borderColor: '#f1f5f9', borderRadius: 10, marginBottom: 8 },
  cardTitle: { fontWeight: '700', fontSize: 16 },
  stat: { marginTop: 4, color: '#475569' },
  error: { color: '#dc2626', marginTop: 8 },
  refresh: { marginTop: 16, alignItems: 'center' },
  refreshText: { color: '#990000', fontWeight: '700' },
});
